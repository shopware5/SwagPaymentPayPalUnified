<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'id' => 'PAYID-MJF55OY30P44731CY226773H',
    'intent' => 'sale',
    'state' => 'approved',
    'cart' => '1G126719HS857043U',
    'payer' => [
        'payment_method' => 'paypal',
        'status' => 'VERIFIED',
        'payer_info' => [
            'email' => 'buyer@shopware.de',
            'first_name' => 'de',
            'last_name' => 'kunde',
            'payer_id' => 'SYJEAZHUC7W88',
            'shipping_address' => [
                'recipient_name' => 'Max Mustermann',
                'line1' => 'Mustermannstraße 92',
                'city' => 'Schöppingen',
                'state' => '',
                'postal_code' => '48624',
                'country_code' => 'DE',
            ],
            'phone' => '+49 7888411531',
            'country_code' => 'DE',
        ],
    ],
    'transactions' => [
        0 => [
            'amount' => [
                'total' => '71.94',
                'currency' => 'EUR',
                'details' => [
                    'subtotal' => '35.95',
                    'tax' => '0.00',
                    'shipping' => '35.99',
                    'insurance' => '0.00',
                    'handling_fee' => '0.00',
                    'shipping_discount' => '0.00',
                    'discount' => '0.00',
                ],
            ],
            'payee' => [
                'merchant_id' => 'D7RFFDVUU6F7N',
                'email' => 'merchant-de@shopware.com',
            ],
            'description' => 'Cigar Special 40%',
            'item_list' => [
                'items' => [
                    0 => [
                        'name' => 'Cigar Special 40%',
                        'sku' => 'SW10006',
                        'price' => '35.95',
                        'currency' => 'EUR',
                        'tax' => '0.00',
                        'quantity' => 1,
                    ],
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
                    'sale' => [
                        'id' => '43899745LH353813S',
                        'state' => 'completed',
                        'amount' => [
                            'total' => '71.94',
                            'currency' => 'EUR',
                            'details' => [
                                'subtotal' => '35.95',
                                'tax' => '0.00',
                                'shipping' => '35.99',
                                'insurance' => '0.00',
                                'handling_fee' => '0.00',
                                'shipping_discount' => '0.00',
                                'discount' => '0.00',
                            ],
                        ],
                        'payment_mode' => 'INSTANT_TRANSFER',
                        'protection_eligibility' => 'ELIGIBLE',
                        'protection_eligibility_type' => 'ITEM_NOT_RECEIVED_ELIGIBLE,UNAUTHORIZED_PAYMENT_ELIGIBLE',
                        'transaction_fee' => [
                            'value' => '1.72',
                            'currency' => 'EUR',
                        ],
                        'parent_payment' => 'PAYID-MJF55OY30P44731CY226773H',
                        'create_time' => '2022-04-05T06:47:41Z',
                        'update_time' => '2022-04-05T06:47:41Z',
                        'links' => [
                            0 => [
                                'href' => 'https://api.sandbox.paypal.com/v1/payments/sale/43899745LH353813S',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                            1 => [
                                'href' => 'https://api.sandbox.paypal.com/v1/payments/sale/43899745LH353813S/refund',
                                'rel' => 'refund',
                                'method' => 'POST',
                            ],
                            2 => [
                                'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAYID-MJF55OY30P44731CY226773H',
                                'rel' => 'parent_payment',
                                'method' => 'GET',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'failed_transactions' => [
    ],
    'create_time' => '2022-04-05T06:16:26Z',
    'update_time' => '2022-04-05T06:47:41Z',
    'links' => [
        0 => [
            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAYID-MJF55OY30P44731CY226773H',
            'rel' => 'self',
            'method' => 'GET',
        ],
    ],
];
