<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\ControllerRegistration;

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
        $this->assertCount(6, $events);
        $this->assertEquals('onGetBackendControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnified']);
        $this->assertEquals('onGetBackendSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedSettings']);
        $this->assertEquals('onGetBackendGeneralSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedGeneralSettings']);
        $this->assertEquals('onGetBackendExpressSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedExpressSettings']);
        $this->assertEquals('onGetBackendInstallmentsSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedInstallmentsSettings']);
        $this->assertEquals('onGetBackendPlusSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedPlusSettings']);
    }

    public function test_onGetBackendControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendControllerPath();

        $this->assertFileExists($backendControllerPath);

        /** @var \Enlight_Template_Manager $template */
        $template = Shopware()->Container()->get('template');
        $templateDirs = $template->getTemplateDir();

        //Do not use the absolute path, since it's different from machine to machine
        $this->assertContains('/SwagPaymentPayPalUnified/Resources/views/', implode('', $templateDirs));
    }

    public function test_onGetSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendSettingsControllerPath();

        $this->assertFileExists($backendControllerPath);

        /** @var \Enlight_Template_Manager $template */
        $template = Shopware()->Container()->get('template');
        $templateDirs = $template->getTemplateDir();

        //Do not use the absolute path, since it's different from machine to machine
        $this->assertContains('/SwagPaymentPayPalUnified/Resources/views/', implode('', $templateDirs));
    }

    public function test_onGetGeneralSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendGeneralSettingsControllerPath();

        $this->assertFileExists($backendControllerPath);
    }

    public function test_onGetExpressSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendExpressSettingsControllerPath();

        $this->assertFileExists($backendControllerPath);
    }

    public function test_onGetInstallmentsSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendInstallmentsSettingsControllerPath();

        $this->assertFileExists($backendControllerPath);
    }

    public function test_onGetPlusSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendPlusSettingsControllerPath();

        $this->assertFileExists($backendControllerPath);
    }
}
