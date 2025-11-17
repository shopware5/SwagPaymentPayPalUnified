<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\WebhookHandlers;

use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\OrderDataServiceResults\OrderAndPaymentStatusResult;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookEventTypes;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookException;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookHandler;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;

class PaymentCaptureDenied extends AbstractWebhook implements WebhookHandler
{
    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var PaymentStatusService
     */
    private $paymentStatusService;

    /**
     * @var OrderDataService
     */
    private $orderDataService;

    public function __construct(
        LoggerServiceInterface $logger,
        PaymentStatusService $paymentStatusService,
        OrderDataService $orderDataService
    ) {
        $this->paymentStatusService = $paymentStatusService;
        $this->logger = $logger;
        $this->orderDataService = $orderDataService;

        parent::__construct($this->logger, $this->orderDataService);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventType()
    {
        return WebhookEventTypes::PAYMENT_CAPTURE_DENIED;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(Webhook $webhook)
    {
        $shopwareOrderServiceResult = $this->getOrderServiceResult($webhook);

        if (!$shopwareOrderServiceResult instanceof OrderAndPaymentStatusResult) {
            $webhookException = new WebhookException(\sprintf('%s expect OrderAndPaymentStatusResult, got %s', __METHOD__, \gettype($shopwareOrderServiceResult)));
            $webhookException->setEventType($this->getEventType());

            throw $webhookException;
        }

        if ($shopwareOrderServiceResult->getPaymentStatusId() !== Status::PAYMENT_STATE_OPEN) {
            $this->paymentStatusService->updatePaymentStatusV2($shopwareOrderServiceResult->getOrderId(), Status::PAYMENT_STATE_OPEN);
        }

        if ($shopwareOrderServiceResult->getOrderStatusId() !== Status::ORDER_STATE_CLARIFICATION_REQUIRED) {
            $this->orderDataService->setOrderStatus($shopwareOrderServiceResult->getOrderId(), Status::ORDER_STATE_CLARIFICATION_REQUIRED);
        }

        return true;
    }
}
