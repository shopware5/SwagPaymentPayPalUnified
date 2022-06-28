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
use PDO;
use RuntimeException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use Shopware_Components_Config as ShopwareConfig;
use sOrder;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Exception\OrderNotFoundException;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use Zend_Mail_Transport_Exception;

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
     * @var SettingsService
     */
    private $settingsService;

    /**
     * @var sOrder
     */
    private $sOrder;

    /**
     * @var ShopwareConfig
     */
    private $shopwareConfig;

    public function __construct(
        ModelManager $modelManager,
        LoggerServiceInterface $logger,
        Connection $connection,
        SettingsService $settingsService,
        DependencyProvider $dependencyProvider,
        ShopwareConfig $shopwareConfig
    ) {
        $this->modelManager = $modelManager;
        $this->logger = $logger;
        $this->connection = $connection;
        $this->settingsService = $settingsService;
        $this->shopwareConfig = $shopwareConfig;
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

        /** @var Order|null $orderModel */
        $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['temporaryId' => $parentPayment]);

        if (!$orderModel instanceof Order) {
            $this->logger->debug(sprintf('%s ORDER WITH TMP ID: %s NOT FOUND', __METHOD__, $parentPayment));

            throw new OrderNotFoundException('temporaryId', $parentPayment);
        }

        $this->sOrder->setPaymentStatus($orderModel->getId(), $paymentStateId, false, 'Set automatically by PayPal integration');
        $this->updateClearedDate($orderModel->getId());

        $this->logger->debug(sprintf('%s UPDATE PAYMENT STATUS SUCCESSFUL', __METHOD__));
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

        $this->sOrder->setPaymentStatus($shopwareOrderId, $paymentStateId, false, 'Set automatically by PayPal integration');
        $this->updateClearedDate($shopwareOrderId);

        $this->logger->debug(sprintf('%s UPDATE PAYMENT STATUS SUCCESSFUL', __METHOD__));
    }

    /**
     * @param string $shopwareOrderNumber
     *
     * @return void
     */
    public function setOrderAndPaymentStatusForFailedOrder($shopwareOrderNumber)
    {
        $this->logger->debug(sprintf('%s shopwareOrderNumber: %s', __METHOD__, $shopwareOrderNumber));

        $settings = $this->settingsService->getSettings();
        if (!$settings instanceof General) {
            throw new RuntimeException('Could not read general PayPal settings');
        }

        $orderStatusId = $settings->getOrderStatusOnFailedPayment();

        $paymentStatusId = $settings->getPaymentStatusOnFailedPayment();
        $shopwareOrderId = $this->getOrderIdByOrderNumber($shopwareOrderNumber);

        $this->logger->debug(sprintf('%s UPDATE ORDER WITH ID %s AND STATUS ID %s AND PAYMENT STATUS ID %s', __METHOD__, $shopwareOrderId, $orderStatusId, $paymentStatusId));

        $this->updatePaymentStatusV2($shopwareOrderId, $paymentStatusId);

        try {
            $this->sOrder->setOrderStatus(
                $shopwareOrderId,
                $orderStatusId,
                $this->shopwareConfig->get('sendOrderMail'),
                sprintf('Failed PayPal Payment with order number: %s', $shopwareOrderNumber)
            );
        } catch (Zend_Mail_Transport_Exception $exception) {
            $this->logger->error(sprintf('%s CANNOT SEND STATUS MAIL FOR ORDER: %s', __METHOD__, $shopwareOrderNumber));
        }
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
     * @param string $shopwareOrderNumber
     *
     * @return int
     */
    private function getOrderIdByOrderNumber($shopwareOrderNumber)
    {
        return (int) $this->connection->createQueryBuilder()
            ->select(['id'])
            ->from('s_order')
            ->where('ordernumber = :orderNumber')
            ->setParameter('orderNumber', $shopwareOrderNumber)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);
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
