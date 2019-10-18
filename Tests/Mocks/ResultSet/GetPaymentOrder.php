<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks\ResultSet;

class GetPaymentOrder
{
    /**
     * @return array
     */
    public static function get()
    {
        return [
            'id' => 'PAY-4PX53149M52862435LWUYHZY',
            'intent' => 'order',
            'state' => 'approved',
            'payer' => [
                'payment_method' => 'paypal',
                'status' => 'VERIFIED',
                'payer_info' => [
                    'email' => 'buyer@shopware.com',
                    'first_name' => 'TestFirstName',
                    'last_name' => 'TestLastName',
                    'payer_id' => 'BNJDKJVFBCXPJ',
                    'shipping_address' => [
                        'recipient_name' => 'TestFirstName TestLastName',
                        'line1' => 'Ebbinghoff 10',
                        'city' => 'Schöppingen',
                        'state' => '',
                        'postal_code' => '48624',
                        'country_code' => 'DE',
                    ],
                    'phone' => '7884987824',
                    'country_code' => 'DE',
                ],
            ],
            'transactions' => [
                0 => [
                    'amount' => [
                        'total' => '45.94',
                        'currency' => 'EUR',
                        'details' => [
                            'subtotal' => '19.95',
                            'shipping' => '25.99',
                        ],
                    ],
                    'payee' => [
                        'merchant_id' => 'HCKBUJL8YWQZS',
                    ],
                    'item_list' => [
                        'items' => [],
                        'shipping_address' => [
                            'recipient_name' => 'TestFirstName TestLastName',
                            'line1' => 'Ebbinghoff 10',
                            'city' => 'Schöppingen',
                            'state' => '',
                            'postal_code' => '48624',
                            'country_code' => 'DE',
                        ],
                    ],
                    'related_resources' => [
                        0 => [
                            'order' => [
                                'id' => 'O-7LN72097VL6103747',
                                'create_time' => '2019-10-18T10:55:36Z',
                                'update_time' => '2019-10-18T10:55:36Z',
                                'amount' => [
                                    'total' => '45.94',
                                    'currency' => 'EUR',
                                    'details' => [
                                            'subtotal' => '19.95',
                                            'shipping' => '25.99',
                                        ],
                                ],
                                'state' => 'COMPLETED',
                                'links' => [
                                    0 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/orders/O-7LN72097VL6103747',
                                        'rel' => 'self',
                                        'method' => 'GET',
                                    ],
                                    1 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-4PX53149M52862435LWUYHZY',
                                        'rel' => 'parent_payment',
                                        'method' => 'GET',
                                    ],
                                    2 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/orders/O-7LN72097VL6103747/do-void',
                                        'rel' => 'void',
                                        'method' => 'POST',
                                    ],
                                    3 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/orders/O-7LN72097VL6103747/authorize',
                                        'rel' => 'authorization',
                                        'method' => 'POST',
                                    ],
                                    4 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/orders/O-7LN72097VL6103747/capture',
                                        'rel' => 'capture',
                                        'method' => 'POST',
                                    ],
                                ],
                                'parent_payment' => 'PAY-4PX53149M52862435LWUYHZY',
                            ],
                        ],
                        1 => [
                            'capture' => [
                                'id' => '04C84852BW259673Y',
                                'amount' => [
                                    'total' => '45.94',
                                    'currency' => 'EUR',
                                ],
                                'state' => 'completed',
                                'transaction_fee' => [
                                    'value' => '1.22',
                                    'currency' => 'EUR',
                                ],
                                'parent_payment' => 'PAY-4PX53149M52862435LWUYHZY',
                                'create_time' => '2019-10-18T09:22:17Z',
                                'links' => [
                                    0 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/04C84852BW259673Y',
                                        'rel' => 'self',
                                        'method' => 'GET',
                                    ],
                                    1 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/04C84852BW259673Y/refund',
                                        'rel' => 'refund',
                                        'method' => 'POST',
                                    ],
                                    2 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-4PX53149M52862435LWUYHZY',
                                        'rel' => 'parent_payment',
                                        'method' => 'GET',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'create_time' => '2019-10-18T09:20:39Z',
            'update_time' => '2019-10-18T09:22:17Z',
            'links' => [
                0 => [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-4PX53149M52862435LWUYHZY',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
            ],
        ];
    }
}
