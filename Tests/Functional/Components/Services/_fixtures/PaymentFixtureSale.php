<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'id' => 'PAY-TEST',
    'intent' => 'sale',
    'state' => 'approved',
    'cart' => 'TEST',
    'payer' => [
        'payment_method' => 'pay_upon_invoice',
        'status' => 'UNVERIFIED',
        'payer_info' => [
            'email' => 'buyer@shopware.com',
            'first_name' => 'FIRST',
            'last_name' => 'LAST',
            'payer_id' => 'PAYER_ID',
            'shipping_address' => [
                'recipient_name' => 'TEST',
                'line1' => 'TEST_STREET',
                'city' => 'TEST',
                'state' => '',
                'postal_code' => '0000',
                'country_code' => 'DE',
            ],
            'country_code' => 'DE',
        ],
    ],
    'transactions' => [
        0 => [
            'amount' => [
                'total' => '45.94',
                'currency' => 'EUR',
                'details' => [
                    'subtotal' => '5.00',
                    'shipping' => '25.99',
                ],
            ],
            'description' => 'Strandtuch "Ibiza"',
            'invoice_number' => '20049',
            'item_list' => [
                'items' => [
                    0 => [
                        'name' => 'Strandtuch "Ibiza"',
                        'sku' => 'SW10178',
                        'price' => '19.95',
                        'currency' => 'EUR',
                        'tax' => '0.00',
                        'quantity' => 1,
                    ],
                ],
                'shipping_address' => [
                    'recipient_name' => 'TEST',
                    'line1' => 'TEST_STREET',
                    'city' => 'TEST_CITY',
                    'state' => '',
                    'postal_code' => '00001',
                    'country_code' => 'DE',
                ],
            ],
            'related_resources' => [
                0 => [
                    'sale' => [
                        'id' => 'TEST1',
                        'state' => 'partially_refunded',
                        'amount' => [
                            'total' => '45.94',
                            'currency' => 'EUR',
                            'details' => [
                                'subtotal' => '5.00',
                                'shipping' => '25.99',
                            ],
                        ],
                        'payment_mode' => 'INSTANT_TRANSFER',
                        'protection_eligibility' => 'ELIGIBLE',
                        'protection_eligibility_type' => 'ITEM_NOT_RECEIVED_ELIGIBLE,UNAUTHORIZED_PAYMENT_ELIGIBLE',
                        'transaction_fee' => [
                            'value' => '1.22',
                            'currency' => 'EUR',
                        ],
                        'receipt_id' => 'TEST',
                        'parent_payment' => 'PAY-TEST',
                        'create_time' => '2017-01-31T09:53:36Z',
                        'update_time' => '2017-01-31T13:07:06Z',
                        'links' => [
                            0 => [
                                'href' => 'TEST',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                            1 => [
                                'href' => 'TEST',
                                'rel' => 'refund',
                                'method' => 'POST',
                            ],
                            2 => [
                                'href' => 'TEST',
                                'rel' => 'parent_payment',
                                'method' => 'GET',
                            ],
                            3 => [
                                'href' => 'TEST',
                                'rel' => 'payment_instruction',
                                'method' => 'GET',
                            ],
                        ],
                    ],
                ],
                1 => [
                    'refund' => [
                        'id' => 'TEST',
                        'state' => 'completed',
                        'amount' => [
                            'total' => '-5.00',
                            'currency' => 'EUR',
                        ],
                        'parent_payment' => 'PAY-TEST',
                        'sale_id' => 'TEST',
                        'create_time' => '2017-01-31T13:05:57Z',
                        'update_time' => '2017-01-31T13:06:06Z',
                        'links' => [
                            0 => [
                                'href' => 'TEST',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                            1 => [
                                'href' => 'TEST',
                                'rel' => 'parent_payment',
                                'method' => 'GET',
                            ],
                            2 => [
                                'href' => 'TEST',
                                'rel' => 'sale',
                                'method' => 'GET',
                            ],
                        ],
                    ],
                ],
                2 => [
                    'refund' => [
                        'id' => 'TEST3',
                        'state' => 'completed',
                        'amount' => [
                            'total' => '-24.00',
                            'currency' => 'EUR',
                        ],
                        'parent_payment' => 'TEST',
                        'sale_id' => 'TEST',
                        'create_time' => '2017-01-31T13:06:44Z',
                        'update_time' => '2017-01-31T13:07:06Z',
                        'links' => [
                            0 => [
                                'href' => 'TEST',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                            1 => [
                                'href' => 'TEST',
                                'rel' => 'parent_payment',
                                'method' => 'GET',
                            ],
                            2 => [
                                'href' => 'TEST',
                                'rel' => 'sale',
                                'method' => 'GET',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'create_time' => '2017-01-31T09:53:36Z',
    'update_time' => '2017-01-31T13:07:06Z',
    'links' => [
        0 => [
            'href' => '',
            'rel' => 'self',
            'method' => 'GET',
        ],
    ],
    'payment_instruction' => [
        'reference_number' => 'TEST',
        'instruction_type' => 'PAY_UPON_INVOICE',
        'recipient_banking_instruction' => [
            'bank_name' => 'BANK',
            'account_holder_name' => 'TEST',
            'international_bank_account_number' => 'TEST',
            'bank_identifier_code' => 'TEST',
        ],
        'amount' => [
            'value' => '45.94',
            'currency' => 'EUR',
        ],
        'payment_due_date' => '2017-03-02',
        'links' => [
            0 => [
                'href' => 'TEST',
                'rel' => 'self',
                'method' => 'GET',
            ],
        ],
    ],
];
