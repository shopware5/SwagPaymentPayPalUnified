<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\WebhookHandler;

class TestWebhookResource
{
    /**
     * @param string $orderId
     *
     * @return array<string,mixed>
     */
    public static function create($orderId)
    {
        return [
            'supplementary_data' => [
                'related_ids' => [
                    'order_id' => $orderId,
                ],
            ],
        ];
    }
}
