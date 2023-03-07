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
use SwagPaymentPayPalUnified\Tests\Functional\AssertStringContainsTrait;

class BackendRegistrationSubscriberTest extends TestCase
{
    use AssertStringContainsTrait;

    public function testCanBeCreated()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        static::assertNotNull($subscriber);
    }

    public function testGetSubscribedEvents()
    {
        $events = Backend::getSubscribedEvents();
        static::assertCount(7, $events);
        static::assertSame('onGetBackendControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnified']);
        static::assertSame('onGetBackendSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedSettings']);
        static::assertSame('onGetBackendGeneralSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedGeneralSettings']);
        static::assertSame('onGetBackendExpressSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedExpressSettings']);
        static::assertSame('onGetBackendInstallmentsSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedInstallmentsSettings']);
        static::assertSame('onGetBackendPlusSettingsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedPlusSettings']);
        static::assertSame('onGetBackendPayUponInvoiceControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedPayUponInvoiceSettings']);
    }

    public function testOnGetBackendControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendControllerPath();

        static::assertFileExists($backendControllerPath);

        $template = Shopware()->Container()->get('template');
        $templateDirs = $template->getTemplateDir();
        static::assertTrue(\is_array($templateDirs));

        static::assertStringContains($this, '/SwagPaymentPayPalUnified/Resources/views/', \implode('', $templateDirs));
    }

    public function testOnGetSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendSettingsControllerPath();

        static::assertFileExists($backendControllerPath);

        $template = Shopware()->Container()->get('template');
        $templateDirs = $template->getTemplateDir();
        static::assertTrue(\is_array($templateDirs));

        static::assertStringContains($this, '/SwagPaymentPayPalUnified/Resources/views/', \implode('', $templateDirs));
    }

    public function testOnGetGeneralSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendGeneralSettingsControllerPath();

        static::assertFileExists($backendControllerPath);
    }

    public function testOnGetExpressSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendExpressSettingsControllerPath();

        static::assertFileExists($backendControllerPath);
    }

    public function testOnGetInstallmentsSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendInstallmentsSettingsControllerPath();

        static::assertFileExists($backendControllerPath);
    }

    public function testOnGetPlusSettingsControllerPath()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'), Shopware()->Container()->get('template'));
        $backendControllerPath = $subscriber->onGetBackendPlusSettingsControllerPath();

        static::assertFileExists($backendControllerPath);
    }
}
