<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks\ResultSet;

use SwagPaymentPayPalUnified\Tests\Mocks\OrderResourceMock;

class CaptureOrder
{
    /**
     * @return array
     */
    public static function get()
    {
        return [
            'id' => '04C84852BW259673Y',
            'amount' => [
                'total' => '45.94',
                'currency' => 'EUR',
            ],
            'state' => 'completed',
            'custom' => '',
            'transaction_fee' => [
                'value' => '1.22',
                'currency' => 'EUR',
            ],
            'is_final_capture' => true,
            'parent_payment' => OrderResourceMock::PAYPAL_PAYMENT_ID,
            'invoice_number' => '',
            'create_time' => '2019-10-18T09:22:17Z',
            'update_time' => '2019-10-18T09:22:17Z',
            'links' => [
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/04C84852BW259673Y',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/04C84852BW259673Y/refund',
                    'rel' => 'refund',
                    'method' => 'POST',
                ],
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-4PX53149M52862435LWUYHZY',
                    'rel' => 'parent_payment',
                    'method' => 'GET',
                ],
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/orders/O-7LN72097VL6103747',
                    'rel' => 'order',
                    'method' => 'GET',
                ],
            ],
        ];
    }
}
