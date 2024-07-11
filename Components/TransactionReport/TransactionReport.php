<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\TransactionReport;

use DateTime;
use Doctrine\DBAL\Connection;
use Exception;
use GuzzleHttp\Client;
use PDO;
use Shopware\Models\Order\Status;

final class TransactionReport
{
    const TRANSACTION_REPORT_TABLE = 'swag_payment_paypal_unified_transaction_report';
    const API_IDENTIFIER = '9b6f559b-5ca1-4969-b23d-e0aa2c01d562';
    const POST_URL = 'https://api.shopware.com';
    const POST_URL_ENDPOINT = '/shopwarepartners/reports/technology';

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param int $orderId
     *
     * @return void
     */
    public function reportOrder($orderId)
    {
        $this->connection->createQueryBuilder()
            ->insert(self::TRANSACTION_REPORT_TABLE)
            ->setValue('order_id', ':orderId')
            ->setParameter('orderId', $orderId)
            ->execute();
    }

    /**
     * @param string $shopwareVersion
     * @param string $instanceId
     *
     * @return void
     */
    public function report($shopwareVersion, $instanceId, Client $client)
    {
        $reportResult = $this->getReportResult($this->getReportedOrderIds());
        $currencies = $reportResult->getCurrencies();

        foreach ($currencies as $currency) {
            $requestBody = [
                'identifier' => self::API_IDENTIFIER,
                'reportDate' => (new DateTime())->format('Y-m-d\\TH:i:sP'),
                'shopwareVersion' => $shopwareVersion,
                'instanceId' => $instanceId,
                'currency' => $currency,
                'reportDataKeys' => ['turnover' => $reportResult->getTurnover($currency)],
            ];

            try {
                $client->post(self::POST_URL_ENDPOINT, ['json' => $requestBody]);
                $this->deleteReportedOrders($reportResult->getOrderIds($currency));
            } catch (Exception $e) {
                // nothing to do
            }
        }
    }

    /**
     * @return array<int, int>
     */
    private function getReportedOrderIds()
    {
        return $this->connection->createQueryBuilder()
            ->select(['order_id'])
            ->from(self::TRANSACTION_REPORT_TABLE)
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param array<int, int> $reportedOrderIds
     *
     * @return void
     */
    private function deleteReportedOrders(array $reportedOrderIds)
    {
        $this->connection->createQueryBuilder()
            ->delete(self::TRANSACTION_REPORT_TABLE)
            ->where('order_id IN (:reportedOrderIds)')
            ->setParameter('reportedOrderIds', $reportedOrderIds, Connection::PARAM_INT_ARRAY)
            ->execute();
    }

    /**
     * @param array<int, int> $reportedIds
     *
     * @return ReportResult
     */
    private function getReportResult(array $reportedIds)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $result = $queryBuilder->select(['currency', 'id', 'invoice_amount'])
            ->from('s_order')
            ->where('id IN (:ordersToReport)')
            ->andWhere('cleared = :paymentStatus')
            ->andWhere('paymentID IN (:paymentIds)')
            ->setParameter('paymentStatus', Status::PAYMENT_STATE_COMPLETELY_PAID)
            ->setParameter('ordersToReport', $reportedIds, Connection::PARAM_INT_ARRAY)
            ->setParameter('paymentIds', $this->getPaymentIds(), Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(PDO::FETCH_GROUP);

        return new ReportResult($result);
    }

    /**
     * @return array<int, int>
     */
    private function getPaymentIds()
    {
        return $this->connection->createQueryBuilder()
            ->select(['id'])
            ->from('s_core_paymentmeans')
            ->where('name LIKE :paymentName')
            ->setParameter('paymentName', 'SwagPaymentPayPalUnified%')
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);
    }
}
