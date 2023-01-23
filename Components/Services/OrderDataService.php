<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Doctrine\DBAL\Connection;
use PDO;
use sOrder;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Services\OrderDataServiceResults\OrderAndPaymentStatusResult;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use UnexpectedValueException;

class OrderDataService
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var sOrder
     */
    private $sOrderModule;

    public function __construct(
        Connection $dbalConnection,
        LoggerServiceInterface $logger,
        DependencyProvider $dependencyProvider
    ) {
        $this->dbalConnection = $dbalConnection;
        $this->logger = $logger;
        $this->dependencyProvider = $dependencyProvider;

        $sOrderModule = $this->dependencyProvider->getModule('order');
        if (!$sOrderModule instanceof sOrder) {
            throw new UnexpectedValueException('Cannot get sOrder module');
        }

        $this->sOrderModule = $sOrderModule;
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
     *
     * @deprecated in 6.0.2, and will be removed with 7.0.0 without replacement
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
     *
     * @deprecated in 6.0.2, and will be removed with 7.0.0 without replacement
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

        // Since joins are being stripped out, we have to select the correct orderId by a sub query.
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
        $statement = $builder->select('o.transactionId')
            ->from('s_order', 'o')
            ->where('o.ordernumber = :orderNumber')
            ->setParameter(':orderNumber', $orderNumber)
            ->execute();

        return (string) $statement->fetchColumn();
    }

    /**
     * @param int $orderId
     * @param int $newOrderStatusId
     *
     * @return void
     */
    public function setOrderStatus($orderId, $newOrderStatusId)
    {
        $this->logger->debug(\sprintf('Update order status for order with id: %d and statusId %d', $orderId, $newOrderStatusId));

        $this->sOrderModule->setOrderStatus($orderId, $newOrderStatusId, true);
    }

    /**
     * @param string $transactionId
     *
     * @return OrderAndPaymentStatusResult|null
     */
    public function getOrderAndPaymentStatusResultByTransactionId($transactionId)
    {
        $order = $this->dbalConnection->createQueryBuilder()
            ->select(['id', 'status', 'cleared'])
            ->from('s_order')
            ->where('transactionID = :transactionId')
            ->setParameter('transactionId', $transactionId)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);

        if (!\is_array($order)) {
            return null;
        }

        return new OrderAndPaymentStatusResult((int) $order['id'], (int) $order['status'], (int) $order['cleared']);
    }
}
