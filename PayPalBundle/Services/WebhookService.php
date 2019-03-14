<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Services;

use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookException;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookHandler;

class WebhookService
{
    /**
     * @var (WebhookHandler|null)[]
     */
    private $registeredWebhooks;

    /**
     * @throws WebhookException
     */
    public function registerWebhook(WebhookHandler $webhook)
    {
        if ($this->registeredWebhooks[$webhook->getEventType()] !== null) {
            $exception = new WebhookException('The specified event is already registered.');
            $exception->setEventType($webhook->getEventType());
            throw $exception;
        }

        $this->registeredWebhooks[$webhook->getEventType()] = $webhook;
    }

    /**
     * @param WebhookHandler[] $webhooks
     */
    public function registerWebhooks(array $webhooks)
    {
        foreach ($webhooks as $webhook) {
            $this->registerWebhook($webhook);
        }
    }

    /**
     * @see WebhookEventTypes
     *
     * @param string $eventType
     *
     * @throws WebhookException
     *
     * @return WebhookHandler
     */
    public function getWebhookHandler($eventType)
    {
        if ($this->registeredWebhooks[$eventType] === null) {
            $exception = new WebhookException('The specified event-type does not exist.');
            $exception->setEventType($eventType);
            throw $exception;
        }

        return $this->registeredWebhooks[$eventType];
    }

    /**
     * @see WebhookEventTypes
     *
     * @param string $eventType
     *
     * @return bool
     */
    public function handlerExists($eventType)
    {
        return $this->registeredWebhooks[$eventType] !== null;
    }

    /**
     * @return WebhookHandler[]
     */
    public function getWebhookHandlers()
    {
        return $this->registeredWebhooks;
    }
}
