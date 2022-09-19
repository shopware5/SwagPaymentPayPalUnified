<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use DateTime;
use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use sOrder;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Exception\OrderNotFoundException;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;

class PaymentStatusService
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var sOrder
     */
    private $sOrder;

    public function __construct(
        ModelManager $modelManager,
        LoggerServiceInterface $logger,
        Connection $connection,
        DependencyProvider $dependencyProvider
    ) {
        $this->modelManager = $modelManager;
        $this->logger = $logger;
        $this->connection = $connection;
        $this->sOrder = $dependencyProvider->getModule('sOrder');
    }

    /**
     * @param string $parentPayment
     * @param int    $paymentStateId
     *
     * @return void
     */
    public function updatePaymentStatus($parentPayment, $paymentStateId)
    {
        $this->logger->debug(
            sprintf('%s PaymentID: %s PaymentStateID : %d', __METHOD__, $parentPayment, $paymentStateId)
        );

        $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['temporaryId' => $parentPayment]);

        if (!$orderModel instanceof Order) {
            $this->logger->debug(sprintf('%s ORDER WITH TMP ID: %s NOT FOUND', __METHOD__, $parentPayment));

            throw new OrderNotFoundException('temporaryId', $parentPayment);
        }

        $shopwareOrderId = $orderModel->getId();
        $this->setPaymentStatus($shopwareOrderId, $paymentStateId);
    }

    /**
     * @param int $shopwareOrderId
     * @param int $paymentStateId
     *
     * @return void
     */
    public function updatePaymentStatusV2($shopwareOrderId, $paymentStateId)
    {
        $this->logger->debug(
            sprintf('%s ShopwareOrderID: %s PaymentStateID : %d', __METHOD__, $shopwareOrderId, $paymentStateId)
        );

        $this->setPaymentStatus($shopwareOrderId, $paymentStateId);
    }

    /**
     * @param bool  $finalize
     * @param float $amountToCapture
     * @param float $maxCaptureAmount
     *
     * @return int
     */
    public function determinePaymentStausForCapturing($finalize, $amountToCapture, $maxCaptureAmount)
    {
        if ($finalize) {
            return Status::PAYMENT_STATE_COMPLETELY_PAID;
        }

        $amountToCapture = (float) $amountToCapture;
        $maxCaptureAmount = (float) $maxCaptureAmount;

        if ($amountToCapture < $maxCaptureAmount) {
            return Status::PAYMENT_STATE_PARTIALLY_PAID;
        }

        return Status::PAYMENT_STATE_COMPLETELY_PAID;
    }

    /**
     * @param int $shopwareOrderId
     * @param int $paymentStateId
     *
     * @return void
     */
    private function setPaymentStatus($shopwareOrderId, $paymentStateId)
    {
        $status = $this->modelManager->getRepository(Status::class)->find($paymentStateId);
        $sendMail = $status instanceof Status && $status->getSendMail();

        try {
            $this->sOrder->setPaymentStatus(
                $shopwareOrderId,
                $paymentStateId,
                $sendMail,
                'Set automatically by PayPal integration'
            );
        } catch (\Zend_Mail_Transport_Exception $e) {
            $this->logger->error(sprintf('%s CANNOT SEND STATUS MAIL FOR ORDER: %s', __METHOD__, $shopwareOrderId));
        }
        if ($paymentStateId === Status::PAYMENT_STATE_COMPLETELY_PAID || $paymentStateId === Status::PAYMENT_STATE_PARTIALLY_PAID) {
            $this->updateClearedDate($shopwareOrderId);
        }

        $this->logger->debug(sprintf('%s UPDATE PAYMENT STATUS SUCCESSFUL', __METHOD__));
    }

    /**
     * @param int $shopwareOrderId
     *
     * @return void
     */
    private function updateClearedDate($shopwareOrderId)
    {
        $this->connection->createQueryBuilder()
            ->update('s_order')
            ->set('cleareddate', ':clearedDate')
            ->where('id = :orderId')
            ->setParameter('clearedDate', (new DateTime('now'))->format('Y-m-d H:i:s'))
            ->setParameter('orderId', $shopwareOrderId)
            ->execute();
    }
}
