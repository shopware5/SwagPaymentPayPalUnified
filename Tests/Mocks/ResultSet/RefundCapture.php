<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks\ResultSet;

use SwagPaymentPayPalUnified\Tests\Mocks\CaptureResourceMock;

class RefundCapture
{
    /**
     * @return array
     */
    public static function get()
    {
        return [
            'id' => '94348888AY620574L',
            'create_time' => '2019-10-18T09:29:51Z',
            'update_time' => '2019-10-18T09:29:51Z',
            'state' => 'completed',
            'amount' => [
                'total' => '30.00',
                'currency' => 'EUR',
            ],
            'refund_from_transaction_fee' => [
                'currency' => 'EUR',
                'value' => '0.57',
            ],
            'total_refunded_amount' => [
                'currency' => 'EUR',
                'value' => '30.00',
            ],
            'refund_from_received_amount' => [
                'currency' => 'EUR',
                'value' => '29.43',
            ],
            'capture_id' => '64G31703U27089925',
            'parent_payment' => CaptureResourceMock::PAYPAL_PAYMENT_ID,
            'description' => '',
            'links' => [
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/refund/94348888AY620574L',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAYID-LWUWSTI3EB47859H72718943',
                    'rel' => 'parent_payment',
                    'method' => 'GET',
                ],
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/64G31703U27089925',
                    'rel' => 'capture',
                    'method' => 'GET',
                ],
            ],
        ];
    }
}
