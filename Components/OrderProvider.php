<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

use Doctrine\DBAL\Connection;

class OrderProvider
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * @return array<int, array<int, array{id: string, transactionID: string, trackingCode: string, status: string, carrier: string, shopId: string}>>
     */
    public function getNotSyncedTrackingOrders()
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select(
            [
                'sorder.subshopID as shopId',
                'sorder.id',
                'sorder.transactionID',
                'sorder.trackingcode as trackingCode',
                'sorder.status as status',
                'order_attributes.swag_paypal_unified_carrier as carrier',
            ]
        )
            ->from('s_order', 'sorder')
            ->innerJoin('sorder', 's_order_attributes', 'order_attributes', 'sorder.id = order_attributes.orderID')
            ->innerJoin('sorder', 's_core_paymentmeans', 'payment_means', 'sorder.paymentID = payment_means.id')
            ->where('swag_paypal_unified_carrier IS NOT NULL AND swag_paypal_unified_carrier <> \'\'')
            ->andWhere('sorder.status <> -1')
            ->andWhere('order_attributes.swag_paypal_unified_carrier_was_sent = 0')
            ->andWhere('sorder.trackingcode IS NOT NULL AND sorder.trackingcode <> \'\'')
            ->andWhere('payment_means.name in (:names)')
            ->andWhere('sorder.ordertime > DATE_SUB(NOW(), INTERVAL 30 DAY)')
            ->orderBy('sorder.subshopID')
            ->setParameter('names', PaymentMethodProvider::getAllUnifiedNames(), Connection::PARAM_STR_ARRAY);

        $orders = $queryBuilder->execute()->fetchAll();

        $shopOrders = [];
        foreach ($orders as $order) {
            $shopOrders[(int) $order['shopId']][] = $order;
        }

        return $shopOrders;
    }

    /**
     * @param array<string> $ids
     *
     * @return void
     */
    public function setPaypalCarrierSent(array $ids)
    {
        $this->connection->executeQuery(
            'UPDATE s_order_attributes SET swag_paypal_unified_carrier_was_sent = 1 WHERE orderID in (?)',
            [$ids],
            [Connection::PARAM_STR_ARRAY]
        );
    }
}
