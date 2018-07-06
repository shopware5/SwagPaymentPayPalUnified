<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use Doctrine\DBAL\Connection;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\PaymentInstruction;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\FixtureImportTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class OrderDataServiceTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;
    use FixtureImportTestCaseTrait;
    use SettingsHelperTrait;

    const ORDER_NUMBER = 99999;
    const PAYMENT_STATUS_APPROVED = 12;
    const TEST_TRANSACTION_ID = 'FAKE-PAYPAL-TRANSACTION-ID';

    public function test_order_data_service_test_is_available()
    {
        $orderDataService = $this->getOrderDataService();

        $this->assertInstanceOf(OrderDataService::class, $orderDataService);
    }

    public function test_should_update_transaction_id()
    {
        $orderDataService = $this->getOrderDataService();

        $orderDataService->applyTransactionId(self::ORDER_NUMBER, self::TEST_TRANSACTION_ID);

        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedOrder = $dbalConnection->executeQuery('SELECT transactionID FROM s_order WHERE ordernumber="' . self::ORDER_NUMBER . '"')->fetchAll();

        $this->assertEquals(self::TEST_TRANSACTION_ID, $updatedOrder[0]['transactionID']);
    }

    public function test_getTransactionId_returns_correct_id()
    {
        $orderDataService = $this->getOrderDataService();

        $orderDataService->applyTransactionId(self::ORDER_NUMBER, self::TEST_TRANSACTION_ID);

        $this->assertEquals(self::TEST_TRANSACTION_ID, $orderDataService->getTransactionId(self::ORDER_NUMBER));
    }

    public function test_applyPaymentTypeAttribute_invoice()
    {
        $orderDataService = $this->getOrderDataService();

        $payment = new Payment();
        $paymentInstruction = new PaymentInstruction();
        $paymentInstruction->setDueDate('12-12-1991');
        $payment->setPaymentInstruction($paymentInstruction);

        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, $payment);

        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        $this->assertEquals(PaymentType::PAYPAL_INVOICE, $updatedAttribute);
    }

    public function test_applyPaymentTypeAttribute_plus()
    {
        $orderDataService = $this->getOrderDataService();
        $this->createTestSettings();

        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, new Payment());

        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        $this->assertEquals(PaymentType::PAYPAL_PLUS, $updatedAttribute);
    }

    public function test_applyPaymentTypeAttribute_installments()
    {
        $orderDataService = $this->getOrderDataService();

        $payment = new Payment();
        $payer = new Payment\Payer();
        $payer->setExternalSelectedFundingInstrumentType('CREDIT');
        $payment->setPayer($payer);

        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, $payment);

        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        $this->assertEquals(PaymentType::PAYPAL_INSTALLMENTS, $updatedAttribute);
    }

    public function test_applyPaymentAttribute_classic()
    {
        $orderDataService = $this->getOrderDataService();
        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, new Payment());

        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        $this->assertEquals(PaymentType::PAYPAL_CLASSIC, $updatedAttribute);
    }

    public function test_applyPaymentAttribute_express_checkout()
    {
        $orderDataService = $this->getOrderDataService();
        $orderDataService->applyPaymentTypeAttribute(self::ORDER_NUMBER, new Payment(), true);

        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $updatedAttribute = $dbalConnection->executeQuery('SELECT swag_paypal_unified_payment_type FROM s_order_attributes WHERE orderID=9999')->fetchColumn();

        $this->assertEquals(PaymentType::PAYPAL_EXPRESS, $updatedAttribute);
    }

    private function createTestSettings()
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'clientId' => 'TEST',
            'clientSecret' => 'TEST',
            'sandbox' => 1,
            'showSidebarLogo' => 'TEST',
            'logoImage' => 'TEST',
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
