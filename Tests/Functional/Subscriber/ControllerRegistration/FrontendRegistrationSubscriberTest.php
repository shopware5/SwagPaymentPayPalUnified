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
    public function testCanBeCreated()
    {
        $subscriber = new Frontend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));
        static::assertNotNull($subscriber);
    }

    public function testGetSubscribedEvents()
    {
        $events = Frontend::getSubscribedEvents();
        static::assertCount(6, $events);
        static::assertSame('onGetWebhookControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedWebhook']);
        static::assertSame('onGetUnifiedControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnified']);
        static::assertSame('onGetUnifiedControllerPathV2', $events['Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedV2']);
        static::assertSame('onGetUnifiedV2PayUponInvoiceControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedV2PayUponInvoice']);
        static::assertSame('onGetPaypalUnifiedApmControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedApm']);
        static::assertSame('onGetPaypalUnifiedAdvancedCreditDebitCardControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedAdvancedCreditDebitCard']);
    }

    public function testOnGetWebhookControllerPath()
    {
        $subscriber = new Frontend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));
        $path = $subscriber->onGetWebhookControllerPath();

        static::assertFileExists($path);
    }

    public function testOnGetFrontendControllerPath()
    {
        $subscriber = new Frontend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));
        $path = $subscriber->onGetUnifiedControllerPath();

        static::assertFileExists($path);
    }
}
