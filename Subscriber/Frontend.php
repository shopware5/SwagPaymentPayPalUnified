<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_View_Default;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class Frontend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDir;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @param string $pluginDir
     */
    public function __construct(
        $pluginDir,
        SettingsServiceInterface $settingsService,
        Connection $connection
    ) {
        $this->pluginDir = $pluginDir;
        $this->settingsService = $settingsService;
        $this->connection = $connection;
        $this->paymentMethodProvider = new PaymentMethodProvider();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Theme_Compiler_Collect_Plugin_Javascript' => 'onCollectJavascript',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatchSecure',
            'Theme_Inheritance_Template_Directories_Collected' => 'onCollectTemplateDir',
        ];
    }

    /**
     * @return ArrayCollection
     */
    public function onCollectJavascript()
    {
        $jsPath = [
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.payment-wall-confirm.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.payment-wall-shipping-payment.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.payment-wall.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.custom-shipping-payment.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.ajax-installments.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.installments-modal.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.express-checkout-button.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.express-checkout-button-in-context.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.in-context-checkout.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.smart-payment-buttons.js',
        ];

        return new ArrayCollection($jsPath);
    }

    /**
     * Handles the Enlight_Controller_Action_PostDispatchSecure_Frontend.
     * Adds the template directory to the TemplateManager
     */
    public function onPostDispatchSecure(\Enlight_Controller_ActionEventArgs $args)
    {
        if (!$this->settingsService->hasSettings()) {
            return;
        }

        $active = (bool) $this->settingsService->get('active');
        if (!$active) {
            return;
        }

        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        $showPayPalLogo = $swUnifiedActive && (bool) $this->settingsService->get('show_sidebar_logo');

        $advertiseReturns = $swUnifiedActive && (bool) $this->settingsService->get('advertise_returns');

        $swUnifiedInstallmentsActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);
        $installmentsActive = (bool) $this->settingsService->get('active', SettingsTable::INSTALLMENTS);
        $showInstallmentsLogoSetting = (bool) $this->settingsService->get('show_logo', SettingsTable::INSTALLMENTS);
        $showInstallmentsLogo = $swUnifiedInstallmentsActive && $installmentsActive && $showInstallmentsLogoSetting;

        /** @var Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        //Assign shop specific and configurable values to the view.
        $view->assign('paypalUnifiedShowLogo', $showPayPalLogo);
        $view->assign('paypalUnifiedAdvertiseReturns', $advertiseReturns);
        $view->assign('paypalUnifiedShowInstallmentsLogo', $showInstallmentsLogo);
    }

    public function onCollectTemplateDir(\Enlight_Event_EventArgs $args)
    {
        $dirs = $args->getReturn();
        $dirs[] = $this->pluginDir . '/Resources/views/';

        $args->setReturn($dirs);
    }
}
