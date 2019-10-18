<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks\ResultSet;

use SwagPaymentPayPalUnified\Tests\Mocks\AuthorizationResourceMock;

class CaptureAuthorization
{
    /**
     * @return array
     */
    public static function get()
    {
        return [
            'id' => '64G31703U27089925',
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
            'parent_payment' => AuthorizationResourceMock::PAYPAL_PAYMENT_ID,
            'invoice_number' => '',
            'create_time' => '2019-10-18T07:37:33Z',
            'update_time' => '2019-10-18T07:37:33Z',
            'links' => [
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/64G31703U27089925',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/64G31703U27089925/refund',
                    'rel' => 'refund',
                    'method' => 'POST',
                ],
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/authorization/6T627190P76232603',
                    'rel' => 'authorization',
                    'method' => 'GET',
                ],
                [
                    'href' => 'https://api.sandbox.paypal.om/v1/payments/payment/PAYID-LWUWSTI3EB47859H72718943',
                    'rel' => 'parent_payment',
                    'method' => 'GET',
                ],
            ],
        ];
    }
}
