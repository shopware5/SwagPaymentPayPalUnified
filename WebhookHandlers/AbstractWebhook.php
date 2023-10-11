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
use UnexpectedValueException;

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

        try {
            $orderAndTransactionIdResult = $this->getOrderAndTransactionIdFromResource($resource);
        } catch (UnexpectedValueException $exception) {
            $this->logger->debug(sprintf('[Webhook]Event: %s. Resource structure is not valid. Message: %s', $this->getEventType(), $exception->getMessage()));

            return null;
        }

        $shopwareOrderServiceResult = $this->orderDataService->getOrderAndPaymentStatusResultByOrderAndTransactionId($orderAndTransactionIdResult);

        if (!$shopwareOrderServiceResult instanceof OrderAndPaymentStatusResult) {
            $this->logger->error(
                sprintf(
                    '[Webhook]Event: %s. Cannot find orderID by PayPalOrderId %s and transactionID: %s',
                    $this->getEventType(),
                    $orderAndTransactionIdResult->getOrderId(),
                    $orderAndTransactionIdResult->getTransactionId()
                ),
                $webhook->toArray()
            );

            return null;
        }

        return $shopwareOrderServiceResult;
    }

    /**
     * @param array<string,mixed> $resource
     *
     * @return OrderAndTransactionIdResult
     */
    private function getOrderAndTransactionIdFromResource(array $resource)
    {
        if (!\array_key_exists('supplementary_data', $resource) || !\is_array($resource['supplementary_data'])) {
            throw new UnexpectedValueException('Expect resource has array key "supplementary_data" with array value');
        }

        $supplementaryData = $resource['supplementary_data'];
        if (!\array_key_exists('related_ids', $supplementaryData) || !\is_array($supplementaryData['related_ids'])) {
            throw new UnexpectedValueException('Expect supplementary_data has array key "related_ids" with array value');
        }

        $relatedIds = $supplementaryData['related_ids'];
        if (!\array_key_exists('order_id', $relatedIds) || !\is_string($relatedIds['order_id'])) {
            throw new UnexpectedValueException('Expect related_ids has array key "order_id" with string value');
        }

        return new OrderAndTransactionIdResult($relatedIds['order_id'], $resource['id']);
    }
}
