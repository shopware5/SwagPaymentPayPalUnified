<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\PaymentInstruction;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\FixtureImportTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class OrderDataServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use FixtureImportTestCaseTrait;

    const ORDER_NUMBER = 99999;
    const TEST_TRANSACTION_ID = 'FAKE-PAYPAL-TRANSACTION-ID';

    public function test_order_data_service_test_is_available()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();

        static::assertInstanceOf(OrderDataService::class, $orderDataService);
    }

    public function test_order_cleared_date_is_set()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();

        $orderDataService->setClearedDate(self::ORDER_NUMBER);

        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $orderCleared = (bool) $dbalConnection->executeQuery('SELECT * FROM s_order AS o WHERE o.cleareddate IS NOT NULL AND o.ordernumber="' . self::ORDER_NUMBER . '"')->fetchAll();

        static::assertTrue($orderCleared);
    }

    public function test_should_update_transaction_id()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();

        $orderDataService->applyTransactionId(self::ORDER_NUMBER, self::TEST_TRANSACTION_ID);

        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedOrder = $dbalConnection->executeQuery('SELECT transactionID FROM s_order WHERE ordernumber="' . self::ORDER_NUMBER . '"')->fetchAll();

        static::assertSame(self::TEST_TRANSACTION_ID, $updatedOrder[0]['transactionID']);
    }

    public function test_getTransactionId_returns_correct_id()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();

        $orderDataService->applyTransactionId(self::ORDER_NUMBER, self::TEST_TRANSACTION_ID);

        static::assertSame(self::TEST_TRANSACTION_ID, $orderDataService->getTransactionId(self::ORDER_NUMBER));
    }

    public function test_applyPaymentTypeAttribute_invoice()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();

        $payment = new Payment();
        $paymentInstruction = new PaymentInstruction();
        $paymentInstruction->setDueDate('12-12-1991');
        $payment->setPaymentInstruction($paymentInstruction);

        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, $payment);

        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        static::assertSame(PaymentType::PAYPAL_INVOICE, $updatedAttribute);
    }

    public function test_applyPaymentTypeAttribute_plus()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();
        $this->createTestSettings();

        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, new Payment());

        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        static::assertSame(PaymentType::PAYPAL_PLUS, $updatedAttribute);
    }

    public function test_applyPaymentAttribute_classic()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();
        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, new Payment());

        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        static::assertSame(PaymentType::PAYPAL_CLASSIC, $updatedAttribute);
    }

    public function test_applyPaymentAttribute_express_checkout()
    {
        $this->importFixturesBefore();

        $orderDataService = $this->getOrderDataService();
        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, new Payment(), true);

        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        static::assertSame(PaymentType::PAYPAL_EXPRESS, $updatedAttribute);
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
}
