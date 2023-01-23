<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\WebhookHandler\_mocks;

use SwagPaymentPayPalUnified\Components\Services\OrderDataServiceResults\OrderAndPaymentStatusResult;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookEventTypes;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;
use SwagPaymentPayPalUnified\WebhookHandlers\AbstractWebhook;

class AbstractWebhookMock extends AbstractWebhook
{
    /**
     * {@inheritdoc}
     */
    public function getEventType()
    {
        return WebhookEventTypes::BILLING_PLAN_CREATED;
    }

    /**
     * @return OrderAndPaymentStatusResult|null
     */
    public function getResult(Webhook $webhook)
    {
        return $this->getOrderServiceResult($webhook);
    }
}
