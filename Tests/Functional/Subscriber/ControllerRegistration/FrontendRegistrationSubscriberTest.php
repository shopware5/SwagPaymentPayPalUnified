<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\ControllerRegistration;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Subscriber\ControllerRegistration\Frontend;

class FrontendRegistrationSubscriberTest extends TestCase
{
    public function test_can_be_created()
    {
        $subscriber = new Frontend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));
        static::assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $events = Frontend::getSubscribedEvents();
        static::assertCount(2, $events);
        static::assertSame('onGetWebhookControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedWebhook']);
        static::assertSame('onGetUnifiedControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnified']);
    }

    public function test_onGetWebhookControllerPath()
    {
        $subscriber = new Frontend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));
        $path = $subscriber->onGetWebhookControllerPath();

        static::assertFileExists($path);
    }

    public function test_onGetFrontendControllerPath()
    {
        $subscriber = new Frontend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));
        $path = $subscriber->onGetUnifiedControllerPath();

        static::assertFileExists($path);
    }
}
