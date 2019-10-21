<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks\ResultSet;

use SwagPaymentPayPalUnified\Tests\Mocks\OrderResourceMock;

class VoidOrder
{
    /**
     * @return array
     */
    public static function get()
    {
        return [
            'id' => 'O-5G515550L5450231X',
            'create_time' => '2019-03-12T14:29:34Z',
            'update_time' => '2019-03-12T14:29:34Z',
            'state' => 'voided',
            'amount' => [
                'total' => '21.85',
                'currency' => 'EUR',
                'details' => [
                    'subtotal' => '17.95',
                    'shipping' => '3.90',
                ],
            ],
            'parent_payment' => OrderResourceMock::PAYPAL_PAYMENT_ID,
            'links' => [
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/orders/O-5G515550L5450231X',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-5AU30554LV4708354LSD4ELY',
                    'rel' => 'parent_payment',
                    'method' => 'GET',
                ],
            ],
        ];
    }
}
