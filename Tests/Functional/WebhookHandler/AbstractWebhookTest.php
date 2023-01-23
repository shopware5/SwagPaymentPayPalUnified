<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\WebhookHandler;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\OrderDataServiceResults\OrderAndPaymentStatusResult;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\WebhookHandler\_mocks\AbstractWebhookMock;

class AbstractWebhookTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testGetOrderServiceResultWithoutResourceShouldReturnNull()
    {
        $abstractWebhook = $this->createAbstractWebhook(true);

        $result = $abstractWebhook->getResult(new Webhook());

        static::assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetOrderServiceResultResourceIdIsNotSetShouldReturnNull()
    {
        $abstractWebhook = $this->createAbstractWebhook(true);

        $webhook = new Webhook();
        $webhook->setResource([]);

        $result = $abstractWebhook->getResult($webhook);

        static::assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetOrderServiceResultOrderServiceReturnsNullShouldReturnNull()
    {
        $abstractWebhook = $this->createAbstractWebhook(true);

        $webhook = new Webhook();
        $webhook->setResource(['id' => 'anyPayPalOrderID']);

        $result = $abstractWebhook->getResult($webhook);

        static::assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetOrderServiceResultShouldReturnOrderServiceResult()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->exec($sql);

        $abstractWebhook = $this->createAbstractWebhook();

        $webhook = new Webhook();
        $webhook->setResource(['id' => 'unitTestTransactionId']);

        $result = $abstractWebhook->getResult($webhook);

        static::assertInstanceOf(OrderAndPaymentStatusResult::class, $result);
        static::assertNotNull($result->getOrderId());
        static::assertSame(-1, $result->getOrderStatusId());
        static::assertSame(1, $result->getPaymentStatusId());
    }

    /**
     * @param bool $loggerExpectCallErrorMethod
     *
     * @return AbstractWebhookMock
     */
    private function createAbstractWebhook($loggerExpectCallErrorMethod = false)
    {
        $logger = $this->createMock(LoggerServiceInterface::class);

        if ($loggerExpectCallErrorMethod) {
            $logger->expects(static::once())->method('error');
        }

        return new AbstractWebhookMock(
            $logger,
            $this->getContainer()->get('paypal_unified.order_data_service')
        );
    }
}
