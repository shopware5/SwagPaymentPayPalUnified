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
        $abstractWebhook = $this->createAbstractWebhook(true, 'error');

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
        $abstractWebhook = $this->createAbstractWebhook(true, 'error');

        $webhook = new Webhook();
        $webhook->setResource(TestWebhookResource::create('anyPayPalOrderID'));

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
        $webhook->setResource(TestWebhookResource::create('unitTestTransactionId'));

        $result = $abstractWebhook->getResult($webhook);

        static::assertInstanceOf(OrderAndPaymentStatusResult::class, $result);
        static::assertNotNull($result->getOrderId());
        static::assertSame(-1, $result->getOrderStatusId());
        static::assertSame(1, $result->getPaymentStatusId());
    }

    /**
     * @return void
     */
    public function testGetOrderServiceResultLogErrorWithResourceArrayWithoutSupplementaryData()
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
    public function testGetOrderServiceResultLogErrorWithResourceArrayWithSupplementaryDataIsNotAnArray()
    {
        $abstractWebhook = $this->createAbstractWebhook(true);

        $webhook = new Webhook();
        $webhook->setResource(['supplementary_data' => '']);

        $result = $abstractWebhook->getResult($webhook);

        static::assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetOrderServiceResultLogErrorWithResourceArrayWithRelatedIdsIsNotAnArray()
    {
        $abstractWebhook = $this->createAbstractWebhook(true);

        $webhook = new Webhook();
        $webhook->setResource(['supplementary_data' => ['related_ids' => '']]);

        $result = $abstractWebhook->getResult($webhook);

        static::assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetOrderServiceResultLogErrorWithResourceArrayWithoutRelatedIds()
    {
        $abstractWebhook = $this->createAbstractWebhook(true);

        $webhook = new Webhook();
        $webhook->setResource(['supplementary_data' => []]);

        $result = $abstractWebhook->getResult($webhook);

        static::assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetOrderServiceResultLogErrorWithResourceArrayWithoutOrderId()
    {
        $abstractWebhook = $this->createAbstractWebhook(true);

        $webhook = new Webhook();
        $webhook->setResource(['supplementary_data' => ['related_ids' => []]]);

        $result = $abstractWebhook->getResult($webhook);

        static::assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetOrderServiceResultLogErrorWithResourceArrayWithOrderIdIsNotAString()
    {
        $abstractWebhook = $this->createAbstractWebhook(true);

        $webhook = new Webhook();
        $webhook->setResource(['supplementary_data' => ['related_ids' => ['order_id' => false]]]);

        $result = $abstractWebhook->getResult($webhook);

        static::assertNull($result);
    }

    /**
     * @param bool   $loggerExpectCallErrorMethod
     * @param string $method
     *
     * @return AbstractWebhookMock
     */
    private function createAbstractWebhook($loggerExpectCallErrorMethod = false, $method = 'debug')
    {
        $logger = $this->createMock(LoggerServiceInterface::class);

        if ($loggerExpectCallErrorMethod) {
            $logger->expects(static::once())->method($method);
        }

        return new AbstractWebhookMock(
            $logger,
            $this->getContainer()->get('paypal_unified.order_data_service')
        );
    }
}
