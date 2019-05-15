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
    public function test_service_available()
    {
        $service = Shopware()->Container()->get('paypal_unified.webhook_service');

        static::assertNotNull($service);
    }

    public function test_register_webhook()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(Shopware()->Container()->get('paypal_unified.logger_service'), Shopware()->Container()->get('models'));

        $service->registerWebhooks([$webhook]);

        static::assertEquals($webhook->getEventType(), $service->getWebhookHandler($webhook->getEventType())->getEventType());
    }

    public function test_register_webhook_exception()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(Shopware()->Container()->get('paypal_unified.logger_service'), Shopware()->Container()->get('models'));

        $service->registerWebhook($webhook);

        $this->expectException(WebhookException::class);
        $service->registerWebhook($webhook);
    }

    public function test_webhook_exists()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(Shopware()->Container()->get('paypal_unified.logger_service'), Shopware()->Container()->get('models'));

        $service->registerWebhook($webhook);

        static::assertTrue($service->handlerExists($webhook->getEventType()));
    }

    public function test_webhook_not_exist()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(Shopware()->Container()->get('paypal_unified.logger_service'), Shopware()->Container()->get('models'));

        $service->registerWebhook($webhook);

        static::assertFalse($service->handlerExists('SHOULD_NOT_EXIST'));
    }

    public function test_get_webhook_exception()
    {
        $service = new WebhookService();

        $this->expectException(WebhookException::class);
        $service->getWebhookHandler(null);
    }

    public function test_get_webhook_handlers()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(Shopware()->Container()->get('paypal_unified.logger_service'), Shopware()->Container()->get('models'));

        $service->registerWebhook($webhook);

        static::assertCount(1, $service->getWebhookHandlers());
    }
}
