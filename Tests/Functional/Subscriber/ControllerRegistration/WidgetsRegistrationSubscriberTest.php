<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\ControllerRegistration;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Subscriber\ControllerRegistration\Widgets;

class WidgetsRegistrationSubscriberTest extends TestCase
{
    public function test_can_be_created()
    {
        $subscriber = new Widgets(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));
        static::assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $events = Widgets::getSubscribedEvents();
        static::assertCount(2, $events);
        static::assertSame('onGetInstallmentsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Widgets_PaypalUnifiedInstallments']);
        static::assertSame('onGetEcControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Widgets_PaypalUnifiedExpressCheckout']);
    }

    public function test_onGetInstallmentsControllerPath()
    {
        $subscriber = new Widgets(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));
        $path = $subscriber->onGetInstallmentsControllerPath();

        static::assertFileExists($path);
    }

    public function test_onGetEcControllerPath()
    {
        $subscriber = new Widgets(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));
        $path = $subscriber->onGetEcControllerPath();

        static::assertFileExists($path);
    }
}
