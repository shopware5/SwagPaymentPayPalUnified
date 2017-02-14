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
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class Frontend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDir;

    /**
     * @var SettingsServiceInterface
     */
    private $config;

    /**
     * @param string                   $pluginDir
     * @param SettingsServiceInterface $config
     */
    public function __construct($pluginDir, SettingsServiceInterface $config)
    {
        $this->pluginDir = $pluginDir;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Theme_Compiler_Collect_Plugin_Javascript' => 'onCollectJavascript',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatchSecure',
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
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.payment-confirm.js',
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
        /** @var Enlight_View_Default $view */
        $view = $args->getSubject()->View();
        $view->addTemplateDir($this->pluginDir . '/Resources/views');

        //Assign shop specific and configurable values to the view.
        $view->assign('showPaypalLogo', $this->config->get('show_sidebar_logo'));
    }
}
