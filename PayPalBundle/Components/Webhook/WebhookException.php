<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook;

class WebhookException extends \Exception
{
    /**
     * The name of the webhook event that triggered this exception
     *
     * @see WebhookEventTypes
     *
     * @var string
     */
    private $eventType;

    /**
     * Sets the event type for this exception.
     * The name of the event specifies the webhook that triggered this exception
     *
     * @see WebhookEventTypes
     *
     * @param string $eventType
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * Returns the name of the event that has caused the exception.
     *
     * @see WebhookEventTypes
     *
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }
}
