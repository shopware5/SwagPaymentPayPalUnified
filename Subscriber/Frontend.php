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
use Enlight_View_Default;
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
     * @param string                   $pluginDir
     * @param SettingsServiceInterface $settingsService
     */
    public function __construct($pluginDir, SettingsServiceInterface $settingsService)
    {
        $this->pluginDir = $pluginDir;
        $this->settingsService = $settingsService;
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
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.ajax-installments.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.custom-shipping-payment.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.express-checkout-button.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.express-checkout-button-in-context.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.in-context-checkout.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.installments-modal.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.payment-wall.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.payment-wall-confirm.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.payment-wall-shipping-payment.js',
        ];

        return new ArrayCollection($jsPath);
    }

    /**
     * Handles the Enlight_Controller_Action_PostDispatchSecure_Frontend.
     * Adds the template directory to the TemplateManager
     *
     * @param \Enlight_Controller_ActionEventArgs $args
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

        $showPayPalLogo = (bool) $this->settingsService->get('show_sidebar_logo');
        $showInstallmentsLogo = (bool) $this->settingsService->get('active', SettingsTable::INSTALLMENTS) && (bool) $this->settingsService->get('show_logo', SettingsTable::INSTALLMENTS);

        /** @var Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        //Assign shop specific and configurable values to the view.
        $view->assign('paypalUnifiedShowLogo', $showPayPalLogo);
        $view->assign('paypalUnifiedShowInstallmentsLogo', $showInstallmentsLogo);
        $view->assign('paypalUnifiedAdvertiseReturns', (bool) $this->settingsService->get('advertise_returns'));
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onCollectTemplateDir(\Enlight_Event_EventArgs $args)
    {
        $dirs = $args->getReturn();
        $dirs[] = $this->pluginDir . '/Resources/views/';

        $args->setReturn($dirs);
    }
}
