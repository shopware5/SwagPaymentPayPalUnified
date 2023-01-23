<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\WebhookHandlers;

use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\OrderDataServiceResults\OrderAndPaymentStatusResult;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookEventTypes;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;

abstract class AbstractWebhook
{
    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var OrderDataService
     */
    private $orderDataService;

    public function __construct(LoggerServiceInterface $logger, OrderDataService $orderDataService)
    {
        $this->logger = $logger;
        $this->orderDataService = $orderDataService;
    }

    /**
     * @return WebhookEventTypes::*
     */
    abstract public function getEventType();

    /**
     * @return OrderAndPaymentStatusResult|null
     */
    protected function getOrderServiceResult(Webhook $webhook)
    {
        $resource = $webhook->getResource();
        if (!\is_array($resource)) {
            $this->logger->error(sprintf('[Webhook]Event: %s. Resource is not an array got: %s', $this->getEventType(), \gettype($resource)), $webhook->toArray());

            return null;
        }

        $transactionId = $resource['id'];
        if (!\is_string($transactionId)) {
            $this->logger->error(sprintf('[Webhook]Event: %s. ResourceID is not an string got: %s', $this->getEventType(), \gettype($transactionId)), $webhook->toArray());

            return null;
        }

        $shopwareOrderServiceResult = $this->orderDataService->getOrderAndPaymentStatusResultByTransactionId($transactionId);

        if (!$shopwareOrderServiceResult instanceof OrderAndPaymentStatusResult) {
            $this->logger->error(sprintf('[Webhook]Event: %s. Cannot find orderID by transactionID: %s', $this->getEventType(), $transactionId), $webhook->toArray());

            return null;
        }

        return $shopwareOrderServiceResult;
    }
}
