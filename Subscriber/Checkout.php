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

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\SDK\Resources\PaymentResource;
use SwagPaymentPayPalUnified\SDK\Structs\Payment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SwagPaymentPayPalUnified\SDK\Services\WebProfileService;
use SwagPaymentPayPalUnified\SDK\Services\ClientService;

class Checkout implements SubscriberInterface
{
    /** @var array $allowedActions */
    private $allowedActions = ['shippingPayment', 'confirm'];

    /** @var ContainerInterface $container */
    protected $container;

    /** @var PaymentMethodProvider $paymentMethodProvider */
    protected $paymentMethodProvider;

    /** @var WebProfileService $profileService */
    protected $profileService;

    /** @var ClientService $clientService */
    protected $clientService;

    /** @var \Shopware_Components_Config $config */
    protected $config;

    /** @var string $pluginDir */
    protected $pluginDir;

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
        $this->clientService = $container->get('paypal_unified.client_service');
        $this->pluginDir = $container->getParameter('paypal_unified.plugin_dir');
    }

    /**
     * Returns the subscribed events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Theme_Compiler_Collect_Plugin_Javascript' => 'onCollectJavascript',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchCheckout'
        ];
    }

    /**
     * @return ArrayCollection
     */
    public function onCollectJavascript()
    {
        $jsPath = [
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.payment-wall-shipping-payment.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.payment-wall.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.payment-confirm.js'
        ];

        return new ArrayCollection($jsPath);
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

        $usePayPalPlus = (bool) $this->config->getByNamespace('SwagPaymentPayPalUnified', 'usePayPalPlus');

        if ($controller->Response()->isRedirect()) {
            return;
        }

        if (!in_array($request->getActionName(), $this->allowedActions)) {
            $session->offsetUnset('PayPalUnifiedCameFromPaymentSelection');
            return;
        }

        if (!$usePayPalPlus) {
            return;
        }

        // Check if the user is coming from checkout step 2 (payment & shipping)
        $cameFromPaymentSelection = $session->get('PayPalUnifiedCameFromPaymentSelection', false);

        if (!$cameFromPaymentSelection) {
            $session->offsetUnset('paypalUnifiedPayment');
        }

        if ($request->getActionName() === 'shippingPayment') {
            $session->offsetSet('PayPalUnifiedCameFromPaymentSelection', true);
        }

        /** @var \Enlight_Controller_Action $controller */
        $view = $controller->View();

        /** @var PaymentResource $paymentResource */
        $paymentResource = $this->container->get('paypal_unified.payment_resource');

        $payment = $paymentResource->create([
            'sBasket' => $view->getAssign('sBasket'),
            'sUserData' => $view->getAssign('sUserData')
        ]);

        /** @var Payment $paymentStruct */
        $paymentStruct = Payment::fromArray($payment);

        if (!empty($paymentStruct->getLinks()->getApprovalUrl())) {
            $view->assign('paypalUnifiedApprovalUrl', $paymentStruct->getLinks()->getApprovalUrl());
        }

        $view->assign('paypalUnifiedModeSandbox', $this->config->getByNamespace('SwagPaymentPayPalUnified', 'enableSandbox'));
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentMethodModel()->getId());
        $view->assign('usePayPalPlus', $usePayPalPlus);
        $view->assign('cameFromPaymentSelection', $cameFromPaymentSelection);
    }
}
