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

namespace SwagPaymentPayPalUnified\Tests\Functional\SDK\Services;

use Shopware\Components\Logger;
use SwagPaymentPayPalUnified\SDK\Components\Webhook\WebhookException;
use SwagPaymentPayPalUnified\SDK\Services\WebhookService;
use SwagPaymentPayPalUnified\WebhookHandlers\SaleComplete;

class WebhookServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_service_available()
    {
        $service = Shopware()->Container()->get('paypal_unified.webhook_service');

        $this->assertNotNull($service);
    }

    public function test_register_webhook()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(new Logger('testlogger'), Shopware()->Container()->get('models'));

        $service->registerWebhook($webhook);

        $this->assertEquals($webhook->getEventType(), $service->getWebhookHandler($webhook->getEventType())->getEventType());
    }

    public function test_register_webhook_exception()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(new Logger('testlogger'), Shopware()->Container()->get('models'));

        $service->registerWebhook($webhook);

        $this->expectException(WebhookException::class);
        $service->registerWebhook($webhook);
    }

    public function test_webhook_exists()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(new Logger('testlogger'), Shopware()->Container()->get('models'));

        $service->registerWebhook($webhook);

        $this->assertTrue($service->handlerExists($webhook->getEventType()));
    }

    public function test_webhook_not_exist()
    {
        $service = new WebhookService();
        $webhook = new SaleComplete(new Logger('testlogger'), Shopware()->Container()->get('models'));

        $service->registerWebhook($webhook);

        $this->assertFalse($service->handlerExists('SHOULD_NOT_EXIST'));
    }

    public function test_get_webhook_exception()
    {
        $service = new WebhookService();

        $this->expectException(WebhookException::class);
        $service->getWebhookHandler(null);
    }
}
