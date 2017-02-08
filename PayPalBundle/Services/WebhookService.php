<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Services;

use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookException;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookHandler;

class WebhookService
{
    /**
     * @var WebhookHandler[]
     */
    private $registeredWebhooks;

    /**
     * @param WebhookHandler $webhook
     *
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
