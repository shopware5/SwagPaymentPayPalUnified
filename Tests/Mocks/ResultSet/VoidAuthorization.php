<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks\ResultSet;

use SwagPaymentPayPalUnified\Tests\Mocks\AuthorizationResourceMock;

class VoidAuthorization
{
    /**
     * @return array
     */
    public static function get()
    {
        return [
            'id' => '8JH509685U6626604',
            'create_time' => '2019-03-13T09:44:34Z',
            'update_time' => '2019-03-13T09:45:52Z',
            'amount' => [
                'total' => '41.85',
                'currency' => 'EUR',
                'details' => [
                    'subtotal' => '37.95',
                    'shipping' => '3.90',
                ],
            ],
            'state' => 'voided',
            'parent_payment' => AuthorizationResourceMock::PAYPAL_PAYMENT_ID,
            'links' => [
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/authorization/8JH509685U6626604',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAYID-LSENBSA4V0732384Y371935U',
                    'rel' => 'parent_payment',
                    'method' => 'GET',
                ],
            ],
        ];
    }
}
