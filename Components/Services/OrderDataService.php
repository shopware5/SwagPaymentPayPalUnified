<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Doctrine\DBAL\Connection;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

class OrderDataService
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    public function __construct(
        Connection $dbalConnection
    ) {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * @param string $orderNumber
     */
    public function setClearedDate($orderNumber)
    {
        $builder = $this->dbalConnection->createQueryBuilder();
        $builder->update('s_order', 'o')
            ->set('o.cleareddate', 'NOW()')
            ->where('o.ordernumber = :orderNumber')
            ->setParameter(':orderNumber', $orderNumber)
            ->execute();
    }

    /**
     * @param string $orderNumber
     * @param int    $orderStateId
     */
    public function setOrderState($orderNumber, $orderStateId)
    {
        $builder = $this->dbalConnection->createQueryBuilder();
        $builder->update('s_order', 'o')
            ->set('o.status', (string) $orderStateId)
            ->where('o.ordernumber = :orderNumber')
            ->setParameter(':orderNumber', $orderNumber)
            ->execute();
    }

    /**
     * @param string $orderNumber
     * @param string $transactionId
     *
     * @return bool
     */
    public function applyTransactionId($orderNumber, $transactionId)
    {
        $result = $this->dbalConnection->createQueryBuilder()->update('s_order', 'o')
            ->set('o.transactionID', ':transactionId')
            ->where('o.ordernumber = :orderNumber')
            ->setParameters([
                ':orderNumber' => $orderNumber,
                ':transactionId' => $transactionId,
            ])
            ->execute();

        return $result === 1;
    }

    /**
     * @param string $orderNumber
     */
    public function removeTransactionId($orderNumber)
    {
        $builder = $this->dbalConnection->createQueryBuilder();
        $builder->update('s_order', 'o')
            ->set('o.transactionID', "''")
            ->where('o.ordernumber = :orderNumber')
            ->setParameter('orderNumber', $orderNumber)
            ->execute();
    }

    /**
     * @param string $orderNumber
     * @param string $paymentType
     *
     * @see PaymentType
     */
    public function applyPaymentTypeAttribute($orderNumber, $paymentType)
    {
        $builder = $this->dbalConnection->createQueryBuilder();

        //Since joins are being stripped out, we have to select the correct orderId by a sub query.
        $subQuery = $this->dbalConnection->createQueryBuilder()
            ->select('o.id')
            ->from('s_order', 'o')
            ->where('o.ordernumber = :orderNumber')
            ->getSQL();

        $builder->update('s_order_attributes', 'oa')
            ->set('oa.swag_paypal_unified_payment_type', ':paymentType')
            ->where('oa.orderID = (' . $subQuery . ')')
            ->setParameters([
                ':orderNumber' => $orderNumber,
                ':paymentType' => $paymentType,
            ])->execute();
    }

    /**
     * @param string $orderNumber
     *
     * @return string
     */
    public function getTransactionId($orderNumber)
    {
        $builder = $this->dbalConnection->createQueryBuilder();
        $builder->select('o.transactionId')
            ->from('s_order', 'o')
            ->where('o.ordernumber = :orderNumber')
            ->setParameter(':orderNumber', $orderNumber);

        return (string) $builder->execute()->fetchColumn();
    }
}
