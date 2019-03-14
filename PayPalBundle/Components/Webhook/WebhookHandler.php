<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;

interface WebhookHandler
{
    /**
     * Returns the name of the webhook event.
     *
     * @see WebhookEventTypes
     *
     * @return string
     */
    public function getEventType();

    /**
     * Invokes the webhook using the provided data.
     *
     * @return bool
     */
    public function invoke(Webhook $webhook);
}
