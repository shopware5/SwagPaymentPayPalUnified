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
use Shopware\Components\HttpClient\RequestException;
use Shopware\Components\Logger;
use Shopware\Models\Shop\DetachedShop;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\PaymentInstructionService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\WebProfileService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Checkout implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @var WebProfileService
     */
    private $profileService;

    /**
     * @var SettingsServiceInterface
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var DetachedShop
     */
    private $shop;

    /**
     * @var array
     */
    private $allowedActions = ['shippingPayment', 'confirm', 'finish'];

    /**
     * Checkout constructor.
     *
     * @param ContainerInterface       $container
     * @param SettingsServiceInterface $config
     * @param DependencyProvider       $dependencyProvider
     */
    public function __construct(ContainerInterface $container, SettingsServiceInterface $config, DependencyProvider $dependencyProvider)
    {
        $this->container = $container;
        $this->config = $config;
        $this->paymentMethodProvider = new PaymentMethodProvider($container->get('models'));
        $this->profileService = $container->get('paypal_unified.web_profile_service');
        $this->logger = $container->get('pluginlogger');
        $this->shop = $dependencyProvider->getShop();
    }

    /**
     * Returns the subscribed events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchCheckout',
        ];
    }

    /**
     * Checks the requirements for the payment wall and assigns the data to the view if the payment wall is displayed.
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatchCheckout(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->getSubject();

        /** @var \Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        /** @var \Enlight_Components_Session_Namespace $session */
        $session = $controller->get('session');

        /** @var \Enlight_View_Default $view */
        $view = $controller->View();

        $action = $request->getActionName();
        $unifiedActive = (bool) $this->config->get('active');
        $usePayPalPlus = (bool) $this->config->get('plus_active');

        if ($controller->Response()->isRedirect() || !$usePayPalPlus || !$unifiedActive) {
            return;
        }

        if (!in_array($action, $this->allowedActions)) {
            $session->offsetUnset('PayPalUnifiedCameFromPaymentSelection');

            return;
        }

        $view->assign('usePayPalPlus', $usePayPalPlus);

        if ($action === 'finish') {
            $this->handleFinishDispatch($view);
        } elseif ($action === 'confirm') {
            $this->handleConfirmDispatch($view, $session);
        } else {
            $this->handleShippingPaymentDispatch($view, $session);
        }

        $errorCode = $request->getParam('paypal_unified_error_code');
        if ($errorCode) {
            $view->assign('paypal_unified_error_code', $errorCode);
        }
    }

    /**
     * Handles the finish dispatch and assigns the payment instructions to the template.
     *
     * @param \Enlight_View_Default $view
     */
    private function handleFinishDispatch(\Enlight_View_Default $view)
    {
        /** @var PaymentInstructionService $instructionService */
        $instructionService = $this->container->get('paypal_unified.payment_instruction_service');

        $selectedPaymentMethod = $view->getAssign('sPayment');
        if ((int) $selectedPaymentMethod['id'] !== $this->paymentMethodProvider->getPaymentId($this->container->get('dbal_connection'))) {
            return;
        }

        $orderNumber = $view->getAssign('sOrderNumber');
        $paymentInstructions = $instructionService->getInstructions($orderNumber);

        if ($paymentInstructions) {
            $paymementInstructionsArray = $paymentInstructions->toArray();
            $view->assign('sTransactionumber', $paymementInstructionsArray['reference']);
            $view->assign('paypalUnifiedPaymentInstructions', $paymementInstructionsArray);
        } else {
            /** @var OrderDataService $orderDataService */
            $orderDataService = $this->container->get('paypal_unified.order_data_service');
            $transactionId = $orderDataService->getTransactionId($orderNumber);
            $view->assign('sTransactionumber', $transactionId);
        }
    }

    /**
     * @param \Enlight_View_Default                 $view
     * @param \Enlight_Components_Session_Namespace $session
     */
    private function handleConfirmDispatch(\Enlight_View_Default $view, \Enlight_Components_Session_Namespace $session)
    {
        // Check if the user is coming from checkout step 2 (payment & shipping)
        $cameFromPaymentSelection = $session->get('PayPalUnifiedCameFromPaymentSelection', false);

        //This value could be set in the shippingPayment action.
        //If so, the payment does not need to be created again.
        $remotePaymentId = $session->get('PayPalUnifiedRemotePaymentId');

        $view->assign('cameFromPaymentSelection', $cameFromPaymentSelection);
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId($this->container->get('dbal_connection')));

        //If the payment has already been created in the payment selection,
        //we don't have to do anything else.
        if ($cameFromPaymentSelection && $remotePaymentId) {
            $view->assign('paypalUnifiedRemotePaymentId', $remotePaymentId);

            return;
        }

        $paymentStruct = $this->createPayment($view->getAssign('sBasket'), $view->getAssign('sUserData'));

        if (!$paymentStruct) {
            return;
        }

        $view->assign('paypalUnifiedModeSandbox', $this->config->get('sandbox'));
        $view->assign('paypalUnifiedRemotePaymentId', $paymentStruct->getId());
        $view->assign('paypalUnifiedApprovalUrl', $paymentStruct->getLinks()[1]->getHref());
        $view->assign('paypalPlusLanguageIso', $this->getPaymentWallLanguage());
    }

    /**
     * @param \Enlight_View_Default                 $view
     * @param \Enlight_Components_Session_Namespace $session
     */
    private function handleShippingPaymentDispatch(\Enlight_View_Default $view, \Enlight_Components_Session_Namespace $session)
    {
        $session->offsetSet('PayPalUnifiedCameFromPaymentSelection', true);
        $paymentStruct = $this->createPayment($view->getAssign('sBasket'), $view->getAssign('sUserData'));

        if (!$paymentStruct) {
            return;
        }

        $view->assign('paypalUnifiedModeSandbox', $this->config->get('sandbox'));
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId($this->container->get('dbal_connection')));
        $view->assign('paypalUnifiedRemotePaymentId', $paymentStruct->getId());
        $view->assign('paypalUnifiedApprovalUrl', $paymentStruct->getLinks()[1]->getHref());
        $view->assign('paypalPlusLanguageIso', $this->getPaymentWallLanguage());

        //Store the paymentID in the session to indicate that
        //the payment has already been created and can be used on the confirm page.
        $session->offsetSet('PayPalUnifiedRemotePaymentId', $paymentStruct->getId());
    }

    /**
     * @param array $basketData
     * @param array $userData
     *
     * @return Payment|null
     */
    private function createPayment(array $basketData, array $userData)
    {
        /** @var PaymentResource $paymentResource */
        $paymentResource = $this->container->get('paypal_unified.payment_resource');

        try {
            $payment = $paymentResource->create(
                [
                    'sBasket' => $basketData,
                    'sUserData' => $userData,
                ]
            );

            return Payment::fromArray($payment);
        } catch (RequestException $ex) {
            $this->logger->error('PayPal Unified: Could not create payment', [$ex->getMessage(), $ex->getBody()]);

            return null;
        }
    }

    /**
     * @return string
     */
    private function getPaymentWallLanguage()
    {
        $languageIso = $this->config->get('plus_language');

        //If no locale ISO was set up specifically,
        //we can use the current shop's locale ISO
        if ($languageIso === null || $languageIso === '') {
            $languageIso = $this->shop->getLocale()->getLocale();
        }

        return $languageIso;
    }
}
