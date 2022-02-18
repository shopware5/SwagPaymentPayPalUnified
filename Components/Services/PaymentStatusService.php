<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Exception\OrderNotFoundException;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
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

    public function __construct(ModelManager $modelManager, LoggerServiceInterface $logger)
    {
        $this->modelManager = $modelManager;
        $this->logger = $logger;
    }

    /**
     * @param string $parentPayment
     * @param int    $paymentStateId
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
        if ($paymentStateId === PaymentStatus::PAYMENT_STATUS_PAID
            || $paymentStateId === PaymentStatus::PAYMENT_STATUS_PARTIALLY_PAID
        ) {
            $orderModel->setClearedDate(new \DateTime());
        }

        $this->modelManager->flush($orderModel);

        $this->logger->debug(sprintf('%s UPDATE PAYMENT STATUS SUCCESSFUL', __METHOD__));
    }
}
