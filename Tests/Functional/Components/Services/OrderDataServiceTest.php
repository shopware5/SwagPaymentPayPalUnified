<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class OrderDataServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

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

        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $orderCleared = (bool) $dbalConnection->executeQuery('SELECT * FROM s_order AS o WHERE o.cleareddate IS NOT NULL AND o.ordernumber="' . self::ORDER_NUMBER . '"')->fetchAll();

        static::assertTrue($orderCleared);
    }

    public function testShouldUpdateTransactionId()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();

        $orderDataService->applyTransactionId(self::ORDER_NUMBER, self::TEST_TRANSACTION_ID);

        $dbalConnection = Shopware()->Container()->get('dbal_connection');
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

        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        static::assertSame(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2, $updatedAttribute);
    }

    public function testApplyPaymentTypeAttributePlus()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();
        $this->createTestSettings();

        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, PaymentType::PAYPAL_PLUS_V2);

        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        static::assertSame(PaymentType::PAYPAL_PLUS_V2, $updatedAttribute);
    }

    public function testApplyPaymentAttributeClassic()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();
        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, PaymentType::PAYPAL_CLASSIC_V2);

        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        static::assertSame(PaymentType::PAYPAL_CLASSIC_V2, $updatedAttribute);
    }

    public function testApplyPaymentAttributeExpressCheckout()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();
        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, PaymentType::PAYPAL_EXPRESS_V2);

        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        static::assertSame(PaymentType::PAYPAL_EXPRESS_V2, $updatedAttribute);
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
        return Shopware()->Container()->get('paypal_unified.order_data_service');
    }

    private function importFixturesBefore()
    {
        $connection = Shopware()->Container()->get('dbal_connection');
        $sql = \file_get_contents(__DIR__ . '/../../order_fixtures.sql');
        static::assertTrue(\is_string($sql));
        $connection->exec($sql);
    }
}
