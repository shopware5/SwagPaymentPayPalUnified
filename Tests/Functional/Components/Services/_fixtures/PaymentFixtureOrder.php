<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'id' => 'PAY-5MU55884RN921074SLCX73MQ',
    'intent' => 'order',
    'state' => 'approved',
    'payer' => [
            'payment_method' => 'paypal',
            'status' => 'VERIFIED',
            'payer_info' => [
                    'email' => 'test@example.com',
                    'first_name' => 'Test',
                    'last_name' => 'Shopware',
                    'payer_id' => 'BWZDBTXXH3264',
                    'shipping_address' => [
                            'recipient_name' => 'Max Mustermann',
                            'line1' => 'Mustermannstraße 92',
                            'city' => 'Schöppingen',
                            'state' => '',
                            'postal_code' => '48624',
                            'country_code' => 'DE',
                        ],
                    'phone' => '7882014168',
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
                    'item_list' => [
                            'items' => [
                                ],
                            'shipping_address' => [
                                    'recipient_name' => 'Max Mustermann',
                                    'line1' => 'Mustermannstraße 92',
                                    'city' => 'Schöppingen',
                                    'state' => '',
                                    'postal_code' => '48624',
                                    'country_code' => 'DE',
                                ],
                        ],
                    'related_resources' => [
                            0 => [
                                    'order' => [
                                            'id' => 'O-34X97336V3049220B',
                                            'create_time' => '2017-03-14T15:53:07Z',
                                            'update_time' => '2017-03-14T15:53:07Z',
                                            'amount' => [
                                                    'total' => '45.94',
                                                    'currency' => 'EUR',
                                                    'details' => [
                                                            'subtotal' => '19.95',
                                                            'shipping' => '25.99',
                                                        ],
                                                ],
                                            'state' => 'CAPTURED',
                                            'links' => [
                                                    0 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/orders/O-34X97336V3049220B',
                                                            'rel' => 'self',
                                                            'method' => 'GET',
                                                        ],
                                                    1 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-5MU55884RN921074SLCX73MQ',
                                                            'rel' => 'parent_payment',
                                                            'method' => 'GET',
                                                        ],
                                                    2 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/orders/O-34X97336V3049220B/do-void',
                                                            'rel' => 'void',
                                                            'method' => 'POST',
                                                        ],
                                                    3 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/orders/O-34X97336V3049220B/authorize',
                                                            'rel' => 'authorization',
                                                            'method' => 'POST',
                                                        ],
                                                    4 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/orders/O-34X97336V3049220B/capture',
                                                            'rel' => 'capture',
                                                            'method' => 'POST',
                                                        ],
                                                ],
                                            'parent_payment' => 'PAY-5MU55884RN921074SLCX73MQ',
                                        ],
                                ],
                            1 => [
                                    'capture' => [
                                            'id' => '1X7048939Y605280U',
                                            'amount' => [
                                                    'total' => '5.00',
                                                    'currency' => 'EUR',
                                                ],
                                            'state' => 'completed',
                                            'transaction_fee' => [
                                                    'value' => '0.45',
                                                    'currency' => 'EUR',
                                                ],
                                            'parent_payment' => 'PAY-5MU55884RN921074SLCX73MQ',
                                            'create_time' => '2017-03-13T13:40:48Z',
                                            'links' => [
                                                    0 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/1X7048939Y605280U',
                                                            'rel' => 'self',
                                                            'method' => 'GET',
                                                        ],
                                                    1 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/1X7048939Y605280U/refund',
                                                            'rel' => 'refund',
                                                            'method' => 'POST',
                                                        ],
                                                    2 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-5MU55884RN921074SLCX73MQ',
                                                            'rel' => 'parent_payment',
                                                            'method' => 'GET',
                                                        ],
                                                ],
                                        ],
                                ],
                            2 => [
                                    'capture' => [
                                            'id' => '5RT63543ND743614L',
                                            'amount' => [
                                                    'total' => '14.00',
                                                    'currency' => 'EUR',
                                                ],
                                            'state' => 'completed',
                                            'transaction_fee' => [
                                                    'value' => '0.62',
                                                    'currency' => 'EUR',
                                                ],
                                            'parent_payment' => 'PAY-5MU55884RN921074SLCX73MQ',
                                            'create_time' => '2017-03-13T13:45:46Z',
                                            'links' => [
                                                    0 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/5RT63543ND743614L',
                                                            'rel' => 'self',
                                                            'method' => 'GET',
                                                        ],
                                                    1 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/5RT63543ND743614L/refund',
                                                            'rel' => 'refund',
                                                            'method' => 'POST',
                                                        ],
                                                    2 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-5MU55884RN921074SLCX73MQ',
                                                            'rel' => 'parent_payment',
                                                            'method' => 'GET',
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                ],
        ],
    'create_time' => '2017-03-14T15:53:07Z',
    'update_time' => '2017-03-13T13:45:46Z',
    'links' => [
            0 => [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-5MU55884RN921074SLCX73MQ',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
        ],
];
