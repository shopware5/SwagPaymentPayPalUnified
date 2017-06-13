<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Components_Session_Namespace as Session;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Shopware\Components\HttpClient\RequestException;
use Shopware\Components\Logger;
use SwagPaymentPayPalUnified\Components\PaymentBuilderInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\ShippingAddressRequestService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAddressPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAmountPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;

class ExpressCheckout implements SubscriberInterface
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var PaymentResource
     */
    private $paymentResource;

    /**
     * @var ShippingAddressRequestService
     */
    private $addressRequestService;

    /**
     * @var PaymentBuilderInterface
     */
    private $paymentBuilder;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param SettingsServiceInterface      $settingsService
     * @param Session                       $session
     * @param PaymentResource               $paymentResource
     * @param ShippingAddressRequestService $addressRequestService
     * @param PaymentBuilderInterface       $paymentBuilder
     * @param Logger                        $pluginLogger
     */
    public function __construct(
        SettingsServiceInterface $settingsService,
        Session $session,
        PaymentResource $paymentResource,
        ShippingAddressRequestService $addressRequestService,
        PaymentBuilderInterface $paymentBuilder,
        Logger $pluginLogger
    ) {
        $this->settingsService = $settingsService;
        $this->session = $session;
        $this->paymentResource = $paymentResource;
        $this->addressRequestService = $addressRequestService;
        $this->paymentBuilder = $paymentBuilder;
        $this->logger = $pluginLogger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'loadExpressCheckoutJS',
            'Enlight_Controller_Action_PostDispatchSecure_Widgets' => 'loadExpressCheckoutJS',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [
                ['addExpressCheckoutButtonCart'],
                ['addEcInfoOnConfirm'],
                ['addPaymentInfoToRequest', 100],
            ],
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'addExpressCheckoutButtonDetail',
        ];
    }

    /**
     * @param ActionEventArgs $args
     */
    public function loadExpressCheckoutJS(ActionEventArgs $args)
    {
        $settings = $this->settingsService->getSettings();
        if (!$settings || !$settings->getActive() || !$settings->getEcActive()) {
            return;
        }

        $view = $args->getSubject()->View();

        $view->assign('paypalExpressCheckoutActive', true);
    }

    /**
     * @param ActionEventArgs $args
     */
    public function addExpressCheckoutButtonCart(ActionEventArgs $args)
    {
        $settings = $this->settingsService->getSettings();
        if (!$settings || !$settings->getActive() || !$settings->getEcActive()) {
            return;
        }

        $action = $args->getRequest()->getActionName();
        $view = $args->getSubject()->View();
        if ($action !== 'cart' && $action !== 'ajaxCart') {
            return;
        }

        $view->assign('paypalUnifiedModeSandbox', $settings->getSandbox());
        $view->assign('paypalUnifiedUseInContext', $settings->getUseInContext());
    }

    /**
     * @param ActionEventArgs $args
     */
    public function addEcInfoOnConfirm(ActionEventArgs $args)
    {
        $request = $args->getRequest();
        $view = $args->getSubject()->View();

        if ($request->getActionName() === 'confirm' && $request->getParam('expressCheckout')) {
            $view->assign('paypalUnifiedExpressCheckout', true);
            $view->assign('paypalUnifiedExpressPaymentId', $request->getParam('paymentId'));
            $view->assign('paypalUnifiedExpressPayerId', $request->getParam('payerId'));
            $view->assign('paypalUnifiedExpressBasketId', $request->getParam('basketId'));
        }
    }

    /**
     * @param ActionEventArgs $args
     */
    public function addPaymentInfoToRequest(ActionEventArgs $args)
    {
        $request = $args->getRequest();

        if ($request->getActionName() === 'payment' &&
            $request->getParam('expressCheckout') &&
            $args->getResponse()->isRedirect()
        ) {
            $paymentId = $request->getParam('paymentId');

            $this->patchAddressAndAmount($paymentId);

            $args->getSubject()->redirect([
                'controller' => 'PaypalUnified',
                'action' => 'return',
                'expressCheckout' => true,
                'paymentId' => $paymentId,
                'PayerID' => $request->getParam('payerId'),
                'basketId' => $request->getParam('basketId'),
            ]);
        }
    }

    /**
     * @param ActionEventArgs $args
     */
    public function addExpressCheckoutButtonDetail(ActionEventArgs $args)
    {
        $settings = $this->settingsService->getSettings();
        if (!$settings ||
            !$settings->getActive() ||
            !$settings->getEcActive() ||
            !$settings->getEcDetailActive()
        ) {
            return;
        }

        $view = $args->getSubject()->View();

        if (!$view->getAssign('userLoggedIn')) {
            $view->assign('paypalExpressCheckoutDetailActive', true);
            $view->assign('paypalUnifiedModeSandbox', $settings->getSandbox());
            $view->assign('paypalUnifiedUseInContext', $settings->getUseInContext());
        }
    }

    /**
     * before the express checkout payment could be executed, the address and amount, which contains the shipping costs,
     * must be updated, because they may have changed during the process
     *
     * @param string $paymentId
     *
     * @throws \Exception
     * @throws RequestException
     */
    private function patchAddressAndAmount($paymentId)
    {
        try {
            $orderVariables = $this->session->get('sOrderVariables');
            $userData = $orderVariables['sUserData'];
            $basketData = $orderVariables['sBasket'];

            $shippingAddress = $this->addressRequestService->getAddress($userData);
            $patch = new PaymentAddressPatch($shippingAddress);
            $this->paymentResource->patch($paymentId, $patch);

            $requestParams = new PaymentBuilderParameters();
            $requestParams->setWebProfileId('temporary');
            $requestParams->setBasketData($basketData);
            $requestParams->setUserData($userData);

            $paymentStruct = $this->paymentBuilder->getPayment($requestParams);
            $amountPatch = new PaymentAmountPatch($paymentStruct->getTransactions()->getAmount());

            $this->paymentResource->patch($paymentId, $amountPatch);
        } catch (RequestException $requestException) {
            $this->logger->error('PayPal Unified ExpressCheckout: Unable to patch the payment (RequestException)', [$requestException->getMessage(), $requestException->getBody()]);
            throw $requestException;
        } catch (\Exception $exception) {
            $this->logger->error('PayPal Unified ExpressCheckout: Unable to patch the payment (Exception)', [$exception->getMessage(), $exception->getTraceAsString()]);
            throw $exception;
        }
    }
}
