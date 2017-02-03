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
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\PaymentInstructionService;
use SwagPaymentPayPalUnified\SDK\Resources\PaymentResource;
use SwagPaymentPayPalUnified\SDK\Structs\Payment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SwagPaymentPayPalUnified\SDK\Services\WebProfileService;

class Checkout implements SubscriberInterface
{
    /** @var array $allowedActions */
    private $allowedActions = ['shippingPayment', 'confirm', 'finish'];

    /** @var ContainerInterface $container */
    protected $container;

    /** @var PaymentMethodProvider $paymentMethodProvider */
    protected $paymentMethodProvider;

    /** @var WebProfileService $profileService */
    protected $profileService;

    /** @var \Shopware_Components_Config $config */
    protected $config;

    /**
     * Checkout constructor.
     * @param ContainerInterface $container
     * @param \Shopware_Components_Config $config
     */
    public function __construct(ContainerInterface $container, \Shopware_Components_Config $config)
    {
        $this->container = $container;
        $this->config = $config;
        $this->paymentMethodProvider = new PaymentMethodProvider($container->get('models'));
        $this->profileService = $container->get('paypal_unified.web_profile_service');
    }

    /**
     * Returns the subscribed events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchCheckout'
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
        $usePayPalPlus = (bool) $this->config->getByNamespace('SwagPaymentPayPalUnified', 'usePayPalPlus');

        if ($controller->Response()->isRedirect() || !$usePayPalPlus) {
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
            $view->assign('sTransactionumber', $paymementInstructionsArray['transactionId']);
            $view->assign('paypalUnifiedPaymentInstructions', $paymementInstructionsArray);
        }
    }

    /**
     * @param \Enlight_View_Default $view
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
            $view->assign('paypalUnifiedRemotePaymentId', $remotePaymentId) ;
            return;
        }

        $paymentStruct = $this->createPayment($view->getAssign('sBasket'), $view->getAssign('sUserData'));

        $view->assign('paypalUnifiedModeSandbox', $this->config->getByNamespace('SwagPaymentPayPalUnified', 'enableSandbox'));
        $view->assign('paypalUnifiedRemotePaymentId', $paymentStruct->getId());
        $view->assign('paypalUnifiedApprovalUrl', $paymentStruct->getLinks()->getApprovalUrl());
    }

    /**
     * @param \Enlight_View_Default $view
     * @param \Enlight_Components_Session_Namespace $session
     */
    private function handleShippingPaymentDispatch(\Enlight_View_Default $view, \Enlight_Components_Session_Namespace $session)
    {
        $session->offsetSet('PayPalUnifiedCameFromPaymentSelection', true);
        $paymentStruct = $this->createPayment($view->getAssign('sBasket'), $view->getAssign('sUserData'));

        $view->assign('paypalUnifiedModeSandbox', $this->config->getByNamespace('SwagPaymentPayPalUnified', 'enableSandbox'));
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId($this->container->get('dbal_connection')));
        $view->assign('paypalUnifiedRemotePaymentId', $paymentStruct->getId());
        $view->assign('paypalUnifiedApprovalUrl', $paymentStruct->getLinks()->getApprovalUrl());

        //Store the paymentID in the session to indicate that
        //the payment has already been created.
        $session->offsetSet('PayPalUnifiedRemotePaymentId', $paymentStruct->getId());
    }

    /**
     * @param array $basketData
     * @param array $userData
     * @return Payment
     */
    private function createPayment(array $basketData, array $userData)
    {
        /** @var PaymentResource $paymentResource */
        $paymentResource = $this->container->get('paypal_unified.payment_resource');

        $payment = $paymentResource->create(
            [
                'sBasket' => $basketData,
                'sUserData' => $userData
            ]
        );

        return Payment::fromArray($payment);
    }
}
