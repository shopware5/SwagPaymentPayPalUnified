<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use DateTime;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Exception\OrderNotFoundException;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use UnexpectedValueException;

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

    public function __construct(ModelManager $modelManager, LoggerServiceInterface $logger)
    {
        $this->modelManager = $modelManager;
        $this->logger = $logger;
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

        if (!($orderModel instanceof Order)) {
            $this->logger->debug(sprintf('%s ORDER WITH TMP ID: %s NOT FOUND', __METHOD__, $parentPayment));

            throw new OrderNotFoundException('temporaryId', $parentPayment);
        }

        /** @var Status|null $orderStatusModel */
        $orderStatusModel = $this->modelManager->getRepository(Status::class)->find($paymentStateId);

        $orderModel->setPaymentStatus($orderStatusModel);
        if ($paymentStateId === Status::PAYMENT_STATE_COMPLETELY_PAID
            || $paymentStateId === Status::PAYMENT_STATE_PARTIALLY_PAID
        ) {
            $orderModel->setClearedDate(new DateTime());
        }

        $this->modelManager->flush($orderModel);

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

        $shopwareOrder = $this->modelManager->getRepository(Order::class)->find($shopwareOrderId);
        if (!$shopwareOrder instanceof Order) {
            $this->logger->debug(sprintf('%s ORDER WITH ID: %s NOT FOUND', __METHOD__, $shopwareOrderId));

            throw new OrderNotFoundException('id', (string) $shopwareOrderId);
        }

        $newOrderPaymentStatus = $this->modelManager->getRepository(Status::class)->find($paymentStateId);
        if (!$newOrderPaymentStatus instanceof Status) {
            $this->logger->debug(sprintf('%s PAYMENT STATUS WITH ID: %d NOT FOUND', __METHOD__, $paymentStateId));

            throw new UnexpectedValueException(sprintf('%s not found by given ID: %d', Status::class, $paymentStateId));
        }

        $shopwareOrder->setPaymentStatus($newOrderPaymentStatus);

        if ($paymentStateId === Status::PAYMENT_STATE_COMPLETELY_PAID || $paymentStateId === Status::PAYMENT_STATE_PARTIALLY_PAID) {
            $shopwareOrder->setClearedDate(new DateTime());
        }

        $this->modelManager->flush($shopwareOrder);

        $this->logger->debug(sprintf('%s UPDATE PAYMENT STATUS SUCCESSFUL', __METHOD__));
    }
}
