<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\PayPalBundle\Services;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookException;
use SwagPaymentPayPalUnified\PayPalBundle\Services\WebhookService;
use SwagPaymentPayPalUnified\WebhookHandlers\SaleComplete;

class WebhookServiceTest extends TestCase
{
    public function testServiceAvailable()
    {
        $service = Shopware()->Container()->get('paypal_unified.webhook_service');

        static::assertNotNull($service);
    }

    public function testRegisterWebhook()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(
            Shopware()->Container()->get('paypal_unified.logger_service'),
            Shopware()->Container()->get('paypal_unified.payment_status_service')
        );

        $service->registerWebhooks([$webhook]);

        static::assertSame(
            $webhook->getEventType(),
            $service->getWebhookHandler($webhook->getEventType())->getEventType()
        );
    }

    public function testRegisterWebhookException()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(
            Shopware()->Container()->get('paypal_unified.logger_service'),
            Shopware()->Container()->get('paypal_unified.payment_status_service')
        );

        $service->registerWebhook($webhook);

        $this->expectException(WebhookException::class);
        $service->registerWebhook($webhook);
    }

    public function testWebhookExists()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(
            Shopware()->Container()->get('paypal_unified.logger_service'),
            Shopware()->Container()->get('paypal_unified.payment_status_service')
        );

        $service->registerWebhook($webhook);

        static::assertTrue($service->handlerExists($webhook->getEventType()));
    }

    public function testWebhookNotExist()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(
            Shopware()->Container()->get('paypal_unified.logger_service'),
            Shopware()->Container()->get('paypal_unified.payment_status_service')
        );

        $service->registerWebhook($webhook);

        static::assertFalse($service->handlerExists('SHOULD_NOT_EXIST'));
    }

    public function testGetWebhookException()
    {
        $service = new WebhookService();

        $this->expectException(WebhookException::class);
        $service->getWebhookHandler(null);
    }

    public function testGetWebhookHandlers()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(
            Shopware()->Container()->get('paypal_unified.logger_service'),
            Shopware()->Container()->get('paypal_unified.payment_status_service')
        );

        $service->registerWebhook($webhook);

        static::assertCount(1, $service->getWebhookHandlers());
    }
}
