<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use PHPUnit\Framework\TestCase;

class TransactionsHistoryBuilderServiceTest extends TestCase
{
    public function testServiceAvailable()
    {
        static::assertNotNull(Shopware()->Container()->get('paypal_unified.transaction_history_builder_service'));
    }

    public function testGetSalesHistoryMaxAmount()
    {
        $historyBuilderService = Shopware()->Container()->get('paypal_unified.transaction_history_builder_service');
        $testPaymentData = $this->getTestSalePaymentDetails();

        $testHistory = $historyBuilderService->getTransactionHistory($testPaymentData);
        static::assertSame(16.939999999999998, $testHistory['maxRefundableAmount']);
    }

    public function testGetSalesHistoryCount()
    {
        $historyBuilderService = Shopware()->Container()->get('paypal_unified.transaction_history_builder_service');
        $testPaymentData = $this->getTestSalePaymentDetails();

        $testHistory = $historyBuilderService->getTransactionHistory($testPaymentData);
        static::assertCount(4, $testHistory);
    }

    public function testGetSalesHistoryFirstEntry()
    {
        $historyBuilderService = Shopware()->Container()->get('paypal_unified.transaction_history_builder_service');
        $testPaymentData = $this->getTestSalePaymentDetails();

        $testSale = $historyBuilderService->getTransactionHistory($testPaymentData)[0];
        static::assertSame(45.94, $testSale['amount']);
        static::assertSame('TEST1', $testSale['id']);
        static::assertSame('partially_refunded', $testSale['state']);
        static::assertSame('2017-01-31T09:53:36Z', $testSale['create_time']);
        static::assertSame('2017-01-31T13:07:06Z', $testSale['update_time']);
        static::assertSame('EUR', $testSale['currency']);
    }

    public function testGetAuthenticationHistoryCount()
    {
        $historyBuilderService = Shopware()->Container()->get('paypal_unified.transaction_history_builder_service');
        $testPaymentData = $this->getTestAuthenticationPaymentDetails();

        $history = $historyBuilderService->getTransactionHistory($testPaymentData);
        static::assertCount(11, $history);
    }

    public function testGetOrderHistoryCount()
    {
        $historyBuilderService = Shopware()->Container()->get('paypal_unified.transaction_history_builder_service');
        $testPaymentData = $this->getTestOrderPaymentDetails();

        $history = $historyBuilderService->getTransactionHistory($testPaymentData);
        static::assertCount(5, $history);
    }

    public function testGetTransactionHistoryException()
    {
        $historyBuilderService = Shopware()->Container()->get('paypal_unified.transaction_history_builder_service');
        $testPaymentData = $this->getTestSalePaymentDetails();
        $testPaymentData['intent'] = 'ERROR';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not parse history from an unknown payment type');
        $historyBuilderService->getTransactionHistory($testPaymentData);
    }

    public function testGetSalesHistoryLastEntry()
    {
        $historyBuilderService = Shopware()->Container()->get('paypal_unified.transaction_history_builder_service');
        $testPaymentData = $this->getTestSalePaymentDetails();

        $testSale = $historyBuilderService->getTransactionHistory($testPaymentData)[2];
        static::assertSame(-24.00, $testSale['amount']);
        static::assertSame('TEST3', $testSale['id']);
        static::assertSame('completed', $testSale['state']);
        static::assertSame('2017-01-31T13:06:44Z', $testSale['create_time']);
        static::assertSame('2017-01-31T13:07:06Z', $testSale['update_time']);
        static::assertSame('EUR', $testSale['currency']);
    }

    /**
     * @return array
     */
    private function getTestSalePaymentDetails()
    {
        return require __DIR__ . '/_fixtures/PaymentFixtureSale.php';
    }

    /**
     * @return array
     */
    private function getTestAuthenticationPaymentDetails()
    {
        return require __DIR__ . '/_fixtures/PaymentFixtureAuthentication.php';
    }

    /**
     * @return array
     */
    private function getTestOrderPaymentDetails()
    {
        return require __DIR__ . '/_fixtures/PaymentFixtureOrder.php';
    }
}
