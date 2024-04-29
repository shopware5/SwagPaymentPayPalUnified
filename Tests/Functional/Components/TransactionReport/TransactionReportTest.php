<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use PDO;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\TransactionReport\TransactionReport;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;

class TransactionReportTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use ContainerTrait;
    use ReflectionHelperTrait;

    /**
     * @return void
     */
    public function testReportOrder()
    {
        $transactionReport = $this->getTransactionReportClass();

        $ids = [
            12,
            42,
            168,
            11589145,
        ];

        foreach ($ids as $id) {
            $transactionReport->reportOrder($id);
        }

        $result = $this->getReported();
        foreach ($ids as $id) {
            static::assertTrue(\in_array($id, $result));
        }
    }

    /**
     * @return void
     */
    public function testReport()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/orders.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->exec($sql);

        $orderIds = $this->getContainer()->get('dbal_connection')
            ->createQueryBuilder()
            ->select(['id'])
            ->from('s_order')
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);

        $clientMock = $this->createMock(Client::class);
        $transactionReport = new TransactionReport($this->getContainer()->get('dbal_connection'));

        foreach ($orderIds as $orderId) {
            $transactionReport->reportOrder($orderId);
        }

        $clientMock->expects(static::exactly(4))->method('post');
        $transactionReport->report('5.7.18', $clientMock);
    }

    /**
     * @return void
     */
    public function testGetReportedOrderIdsWithEmptyResult()
    {
        $transactionReport = $this->getTransactionReportClass();

        $refectionMethod = $this->getReflectionMethod(TransactionReport::class, 'getReportedOrderIds');

        static::assertEmpty($refectionMethod->invoke($transactionReport));
    }

    /**
     * @return void
     */
    public function testGetReportedOrderIds()
    {
        $ids = [
            1,
            2,
            12,
            25697863,
        ];

        $transactionReport = $this->getTransactionReportClass();
        foreach ($ids as $id) {
            $transactionReport->reportOrder($id);
        }

        $refectionMethod = $this->getReflectionMethod(TransactionReport::class, 'getReportedOrderIds');
        $result = $refectionMethod->invoke($transactionReport);

        static::assertNotEmpty($result);
        foreach ($ids as $id) {
            static::assertTrue(\in_array($id, $result));
        }
    }

    /**
     * @return void
     */
    public function testDeleteReportedOrders()
    {
        $ids = [
            5,
            10,
            102,
            365897,
            3256971,
            598765425,
        ];

        $reflectionMethod = $this->getReflectionMethod(TransactionReport::class, 'deleteReportedOrders');

        $transactionReport = $this->getTransactionReportClass();
        static::assertEmpty($this->getReported());

        foreach ($ids as $id) {
            $transactionReport->reportOrder($id);
        }
        $interimResultOne = $this->getReported();
        static::assertCount(6, $interimResultOne);

        $reflectionMethod->invoke($transactionReport, [$ids[0], $ids[1], $ids[2], $ids[5]]);
        $interimResultTwo = $this->getReported();
        static::assertCount(2, $interimResultTwo);

        $reflectionMethod->invoke($transactionReport, $ids);
        $finalResult = $this->getReported();
        static::assertCount(0, $finalResult);
    }

    /**
     * @return void
     */
    public function testGetReportResult()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/orders.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->exec($sql);

        $reflectionMethod = $this->getReflectionMethod(TransactionReport::class, 'getReportResult');

        $transactionReport = $this->getTransactionReportClass();

        $expectedCurrencies = [
            0 => 'EUR',
            1 => 'DKK',
            2 => 'CHF',
            3 => 'USD',
        ];

        $expectedTurnoverResult = [
            'EUR' => 14442.3,
            'DKK' => 11962.28,
            'CHF' => 12478.41,
            'USD' => 15057.28,
        ];

        $result = $reflectionMethod->invoke($transactionReport, $this->getOrderIds($transactionReport));

        $currencies = $result->getCurrencies();
        static::assertCount(4, $currencies);
        foreach ($expectedCurrencies as $expectedCurrency) {
            static::assertContains($expectedCurrency, $currencies);
        }

        foreach ($currencies as $currency) {
            static::assertSame($expectedTurnoverResult[$currency], $result->getTurnover($currency));
            static::assertNotEmpty($result->getOrderIds($currency));
        }
    }

    /**
     * @return void
     */
    public function testGetPaymentIds()
    {
        $transactionReport = $this->getTransactionReportClass();
        $reflectionMethod = $this->getReflectionMethod(TransactionReport::class, 'getPaymentIds');
        $result = $reflectionMethod->invoke($transactionReport);

        static::assertCount(15, $result);
    }

    /**
     * @return TransactionReport
     */
    private function getTransactionReportClass()
    {
        return new TransactionReport($this->getContainer()->get('dbal_connection'));
    }

    /**
     * @return array<int, int>
     */
    private function getOrderIds(TransactionReport $transactionReport)
    {
        $getPaymentIdsMethod = $this->getReflectionMethod(TransactionReport::class, 'getPaymentIds');

        return $this->getContainer()->get('dbal_connection')
            ->createQueryBuilder()
            ->select(['id'])
            ->from('s_order')
            ->where('paymentID IN (:paymentIds)')
            ->setParameter('paymentIds', $getPaymentIdsMethod->invoke($transactionReport), Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return array<int, int>
     */
    private function getReported()
    {
        return $this->getContainer()->get('dbal_connection')
            ->createQueryBuilder()
            ->select(['order_id'])
            ->from('swag_payment_paypal_unified_transaction_report')
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);
    }
}
