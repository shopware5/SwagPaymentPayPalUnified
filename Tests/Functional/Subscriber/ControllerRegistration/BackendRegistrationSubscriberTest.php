<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\ControllerRegistration;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Subscriber\ControllerRegistration\Backend;

class BackendRegistrationSubscriberTest extends TestCase
{
    public function test_can_be_created()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        static::assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $events = Backend::getSubscribedEvents();
        static::assertCount(6, $events);
        static::assertSame('onGetBackendControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnified']);
        static::assertSame('onGetBackendSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedSettings']);
        static::assertSame('onGetBackendGeneralSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedGeneralSettings']);
        static::assertSame('onGetBackendExpressSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedExpressSettings']);
        static::assertSame('onGetBackendInstallmentsSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedInstallmentsSettings']);
        static::assertSame('onGetBackendPlusSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedPlusSettings']);
    }

    public function test_onGetBackendControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendControllerPath();

        static::assertFileExists($backendControllerPath);

        /** @var \Enlight_Template_Manager $template */
        $template = Shopware()->Container()->get('template');
        $templateDirs = $template->getTemplateDir();

        //Do not use the absolute path, since it's different from machine to machine
        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('/SwagPaymentPayPalUnified/Resources/views/', \implode('', $templateDirs));

            return;
        }
        static::assertContains('/SwagPaymentPayPalUnified/Resources/views/', \implode('', $templateDirs));
    }

    public function test_onGetSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendSettingsControllerPath();

        static::assertFileExists($backendControllerPath);

        /** @var \Enlight_Template_Manager $template */
        $template = Shopware()->Container()->get('template');
        $templateDirs = $template->getTemplateDir();

        //Do not use the absolute path, since it's different from machine to machine
        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('/SwagPaymentPayPalUnified/Resources/views/', \implode('', $templateDirs));

            return;
        }
        static::assertContains('/SwagPaymentPayPalUnified/Resources/views/', \implode('', $templateDirs));
    }

    public function test_onGetGeneralSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendGeneralSettingsControllerPath();

        static::assertFileExists($backendControllerPath);
    }

    public function test_onGetExpressSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendExpressSettingsControllerPath();

        static::assertFileExists($backendControllerPath);
    }

    public function test_onGetInstallmentsSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendInstallmentsSettingsControllerPath();

        static::assertFileExists($backendControllerPath);
    }

    public function test_onGetPlusSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendPlusSettingsControllerPath();

        static::assertFileExists($backendControllerPath);
    }
}
