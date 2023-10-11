<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use Generator;
use PDO;
use PHPUnit\Framework\TestCase;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\OrderDataServiceResults\OrderAndPaymentStatusResult;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\WebhookHandlers\OrderAndTransactionIdResult;

class OrderDataServiceTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use ShopRegistrationTrait;

    const ORDER_NUMBER = '99999';
    const TEST_TRANSACTION_ID = 'FAKE-PAYPAL-TRANSACTION-ID';

    public function testOrderDataServiceTestIsAvailable()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();

        static::assertInstanceOf(OrderDataService::class, $orderDataService);
    }

    public function testOrderClearedDateIsSet()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();

        $orderDataService->setClearedDate(self::ORDER_NUMBER);

        $dbalConnection = $this->getContainer()->get('dbal_connection');
        $orderCleared = (bool) $dbalConnection->executeQuery('SELECT * FROM s_order AS o WHERE o.cleareddate IS NOT NULL AND o.ordernumber="' . self::ORDER_NUMBER . '"')->fetchAll();

        static::assertTrue($orderCleared);
    }

    public function testShouldUpdateTransactionId()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();

        $orderDataService->applyTransactionId(self::ORDER_NUMBER, self::TEST_TRANSACTION_ID);

        $dbalConnection = $this->getContainer()->get('dbal_connection');
        $updatedOrder = $dbalConnection->executeQuery('SELECT transactionID FROM s_order WHERE ordernumber="' . self::ORDER_NUMBER . '"')->fetchAll();

        static::assertSame(self::TEST_TRANSACTION_ID, $updatedOrder[0]['transactionID']);
    }

    public function testGetTransactionIdReturnsCorrectId()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();

        $orderDataService->applyTransactionId(self::ORDER_NUMBER, self::TEST_TRANSACTION_ID);

        static::assertSame(self::TEST_TRANSACTION_ID, $orderDataService->getTransactionId(self::ORDER_NUMBER));
    }

    public function testApplyPaymentTypeAttributeInvoice()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();

        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, PaymentType::PAYPAL_PAY_UPON_INVOICE_V2);

        $dbalConnection = $this->getContainer()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        static::assertSame(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2, $updatedAttribute);
    }

    public function testApplyPaymentTypeAttributePlus()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();
        $this->createTestSettings();

        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, PaymentType::PAYPAL_PLUS);

        $dbalConnection = $this->getContainer()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        static::assertSame(PaymentType::PAYPAL_PLUS, $updatedAttribute);
    }

    public function testApplyPaymentAttributeClassic()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();
        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, PaymentType::PAYPAL_CLASSIC_V2);

        $dbalConnection = $this->getContainer()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        static::assertSame(PaymentType::PAYPAL_CLASSIC_V2, $updatedAttribute);
    }

    public function testApplyPaymentAttributeExpressCheckout()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();
        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, PaymentType::PAYPAL_EXPRESS_V2);

        $dbalConnection = $this->getContainer()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        static::assertSame(PaymentType::PAYPAL_EXPRESS_V2, $updatedAttribute);
    }

    /**
     * @dataProvider setOrderStatusTestDataProvider
     *
     * @param int $newOrderStatus
     *
     * @return void
     */
    public function testSetOrderStatus($newOrderStatus)
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order_status.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->exec($sql);

        $orderData = $this->getOrderData();
        static::assertTrue(\is_array($orderData));
        $this->getOrderDataService()->setOrderStatus($orderData['id'], $newOrderStatus);

        $result = $this->getOrderData();
        static::assertTrue(\is_array($result));
        static::assertSame($orderData['id'], $result['id']);
        static::assertSame($newOrderStatus, (int) $result['status']);
    }

    /**
     * @return Generator<array<int,int>>
     */
    public function setOrderStatusTestDataProvider()
    {
        yield 'Order status 1000' => [
            1000,
        ];

        yield 'Status::ORDER_STATE_CANCELLED' => [
            Status::ORDER_STATE_CANCELLED,
        ];

        yield 'Status::ORDER_STATE_OPEN' => [
            Status::ORDER_STATE_OPEN,
        ];

        yield 'Status::ORDER_STATE_IN_PROCESS' => [
            Status::ORDER_STATE_IN_PROCESS,
        ];

        yield 'Status::ORDER_STATE_COMPLETED' => [
            Status::ORDER_STATE_COMPLETED,
        ];

        yield 'Status::ORDER_STATE_CANCELLED_REJECTED' => [
            Status::ORDER_STATE_CANCELLED_REJECTED,
        ];

        yield 'Status::ORDER_STATE_READY_FOR_DELIVERY' => [
            Status::ORDER_STATE_READY_FOR_DELIVERY,
        ];

        yield 'Status::ORDER_STATE_CLARIFICATION_REQUIRED' => [
            Status::ORDER_STATE_CLARIFICATION_REQUIRED,
        ];
    }

    /**
     * @return void
     */
    public function testGetShopwareOrderServiceResultByTransactionIdShouldReturnNull()
    {
        static::assertNull(
            $this->getOrderDataService()->getOrderAndPaymentStatusResultByTransactionId('anyTransactionId')
        );
    }

    /**
     * @return void
     */
    public function testGetShopwareOrderServiceResultByTransactionIdShouldReturnOrderServiceResult()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order_status.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->exec($sql);

        $result = $this->getOrderDataService()->getOrderAndPaymentStatusResultByTransactionId('unitTestTransactionId');

        static::assertInstanceOf(OrderAndPaymentStatusResult::class, $result);
        static::assertTrue(\is_int($result->getOrderId()));
        static::assertSame(-1, $result->getOrderStatusId());
        static::assertSame(1, $result->getPaymentStatusId());
    }

    /**
     * @return void
     */
    public function testGetOrderAndPaymentStatusResultByOrderAndTransactionId()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order_status.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->exec($sql);

        $resultOne = $this->getOrderDataService()->getOrderAndPaymentStatusResultByOrderAndTransactionId(
            new OrderAndTransactionIdResult('unitTestTransactionId', 'any')
        );

        static::assertInstanceOf(OrderAndPaymentStatusResult::class, $resultOne);
        static::assertTrue(\is_int($resultOne->getOrderId()));
        static::assertSame(-1, $resultOne->getOrderStatusId());
        static::assertSame(1, $resultOne->getPaymentStatusId());

        $resultTwo = $this->getOrderDataService()->getOrderAndPaymentStatusResultByOrderAndTransactionId(
            new OrderAndTransactionIdResult('any', 'unitTestTransactionId')
        );

        static::assertInstanceOf(OrderAndPaymentStatusResult::class, $resultTwo);
        static::assertTrue(\is_int($resultTwo->getOrderId()));
        static::assertSame(-1, $resultTwo->getOrderStatusId());
        static::assertSame(1, $resultTwo->getPaymentStatusId());
    }

    /**
     * @return void
     */
    public function testGetOrderAndPaymentStatusResultByOrderAndTransactionIdShouldReturnNull()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order_status.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->exec($sql);

        $result = $this->getOrderDataService()->getOrderAndPaymentStatusResultByOrderAndTransactionId(
            new OrderAndTransactionIdResult('any', 'any')
        );

        static::assertNull($result);
    }

    /**
     * @return array<string,int>|null
     */
    private function getOrderData()
    {
        return $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->select(['id', 'status'])
            ->from('s_order')
            ->where('transactionID = :transactionId')
            ->setParameter('transactionId', 'unitTestTransactionId')
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);
    }

    private function createTestSettings()
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'clientId' => 'TEST',
            'clientSecret' => 'TEST',
            'sandbox' => 1,
            'showSidebarLogo' => 'TEST',
        ]);

        $this->insertPlusSettingsFromArray(['active' => 1]);
    }

    /**
     * @return OrderDataService
     */
    private function getOrderDataService()
    {
        return $this->getContainer()->get('paypal_unified.order_data_service');
    }

    private function importFixturesBefore()
    {
        $connection = $this->getContainer()->get('dbal_connection');
        $sql = \file_get_contents(__DIR__ . '/../../order_fixtures.sql');
        static::assertTrue(\is_string($sql));
        $connection->exec($sql);
    }
}
