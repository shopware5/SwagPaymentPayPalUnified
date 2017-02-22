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

namespace SwagPaymentPayPalUnified\Tests\Functional\WebhookHandler;

use SwagPaymentPayPalUnified\Subscriber\ControllerRegistration\Backend;

class BackendRegistrationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function test_can_be_created()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $events = Backend::getSubscribedEvents();
        $this->assertCount(2, $events);
        $this->assertEquals('onGetBackendControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnified']);
        $this->assertEquals('onGetBackendSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedSettings']);
    }

    public function test_onGetBackendControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendControllerPath();

        $this->assertFileExists($backendControllerPath);

        /** @var \Enlight_Template_Manager $template */
        $template = Shopware()->Container()->get('template');
        $this->assertStringEndsWith('/SwagPaymentPayPalUnified/Resources/views/', $template->getTemplateDir(0)); //We can not use the absolute path, since it's different from machine to machine!
    }

    public function test_onGetSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendSettingsControllerPath();

        $this->assertFileExists($backendControllerPath);

        /** @var \Enlight_Template_Manager $template */
        $template = Shopware()->Container()->get('template');
        $this->assertStringEndsWith('/SwagPaymentPayPalUnified/Resources/views/', $template->getTemplateDir(0)); //We can not use the absolute path, since it's different from machine to machine!
    }
}
