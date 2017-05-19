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
use SwagPaymentPayPalUnified\Components\Services\ShippingAddressRequestService;
use SwagPaymentPayPalUnified\Models\Settings;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAddressPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAmountPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\PaymentRequestServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile;

class ExpressCheckout implements SubscriberInterface
{
    /**
     * @var Settings
     */
    private $settings;

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
     * @var PaymentRequestServiceInterface
     */
    private $paymentRequestService;

    /**
     * @param SettingsServiceInterface       $settingsService       ,
     * @param Session                        $session
     * @param PaymentResource                $paymentResource
     * @param ShippingAddressRequestService  $addressRequestService
     * @param PaymentRequestServiceInterface $paymentRequestService
     */
    public function __construct(
        SettingsServiceInterface $settingsService,
        Session $session,
        PaymentResource $paymentResource,
        ShippingAddressRequestService $addressRequestService,
        PaymentRequestServiceInterface $paymentRequestService
    ) {
        $this->settings = $settingsService->getSettings();
        $this->session = $session;
        $this->paymentResource = $paymentResource;
        $this->addressRequestService = $addressRequestService;
        $this->paymentRequestService = $paymentRequestService;
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
                ['addExpressCheckoutButton'],
                ['addEcInfoOnConfirm'],
                ['addPaymentInfoToRequest', 100],
            ],
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'onPostDispatchDetail',
        ];
    }

    /**
     * @param ActionEventArgs $args
     */
    public function loadExpressCheckoutJS(ActionEventArgs $args)
    {
        if (!$this->settings || !$this->settings->getActive() || !$this->settings->getEcActive()) {
            return;
        }

        $view = $args->getSubject()->View();

        $view->assign('paypalExpressCheckoutActive', true);
    }

    /**
     * @param ActionEventArgs $args
     */
    public function addExpressCheckoutButton(ActionEventArgs $args)
    {
        if (!$this->settings || !$this->settings->getActive() || !$this->settings->getEcActive()) {
            return;
        }

        $action = $args->getRequest()->getActionName();
        $view = $args->getSubject()->View();
        if ($action !== 'cart' && $action !== 'ajaxCart') {
            return;
        }

        $view->assign('paypalUnifiedModeSandbox', $this->settings->getSandbox());
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
            ]);
        }
    }

    /**
     * @param ActionEventArgs $args
     */
    public function onPostDispatchDetail(ActionEventArgs $args)
    {
        if (!$this->settings ||
            !$this->settings->getActive() ||
            !$this->settings->getEcActive() ||
            !$this->settings->getEcDetailActive()
        ) {
            return;
        }

        $view = $args->getSubject()->View();

        $view->assign('paypalExpressCheckoutDetailActive', true);
    }

    /**
     * before the express checkout payment could be executed, the address and amount, which contains the shipping costs,
     * must be updated, because they may have changed during the process
     *
     * @param string $paymentId
     */
    private function patchAddressAndAmount($paymentId)
    {
        $orderVariables = $this->session->get('sOrderVariables');
        $userData = $orderVariables['sUserData'];
        $basketData = $orderVariables['sBasket'];

        $shippingAddress = $this->addressRequestService->getAddress($userData);
        $patch = new PaymentAddressPatch($shippingAddress);
        $this->paymentResource->patch($paymentId, $patch);

        $profile = new WebProfile();
        $profile->setId('fake');
        $paymentStruct = $this->paymentRequestService->getRequestParameters($profile, $basketData, $userData);
        $amountPatch = new PaymentAmountPatch($paymentStruct->getTransactions()->getAmount());
        $this->paymentResource->patch($paymentId, $amountPatch);
    }
}
