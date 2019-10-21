<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks\ResultSet;

class GetPaymentSale
{
    /**
     * @return array
     */
    public static function get()
    {
        return [
            'id' => 'PAYID-LWSWIBI77389724VP530350F',
            'intent' => 'sale',
            'state' => 'approved',
            'cart' => '8WE26342B6270690X',
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
                            'tax' => '0.00',
                            'shipping' => '25.99',
                            'insurance' => '0.00',
                            'handling_fee' => '0.00',
                            'shipping_discount' => '0.00',
                        ],
                    ],
                    'payee' => [
                        'merchant_id' => 'HCKBUJL8YWQZS',
                        'email' => 'info@shopware.de',
                    ],
                    'description' => 'Strandtuch "Ibiza"',
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
                            'sale' => [
                                'id' => '9FY68813RG278922J',
                                'state' => 'partially_refunded',
                                'amount' => [
                                    'total' => '45.94',
                                    'currency' => 'EUR',
                                    'details' => [
                                            'subtotal' => '19.95',
                                            'tax' => '0.00',
                                            'shipping' => '25.99',
                                            'insurance' => '0.00',
                                            'handling_fee' => '0.00',
                                            'shipping_discount' => '0.00',
                                        ],
                                ],
                                'payment_mode' => 'INSTANT_TRANSFER',
                                'protection_eligibility' => 'ELIGIBLE',
                                'protection_eligibility_type' => 'ITEM_NOT_RECEIVED_ELIGIBLE,UNAUTHORIZED_PAYMENT_ELIGIBLE',
                                'transaction_fee' => [
                                    'value' => '1.22',
                                    'currency' => 'EUR',
                                ],
                                'parent_payment' => 'PAYID-LWSWIBI77389724VP530350F',
                                'create_time' => '2019-10-15T06:16:48Z',
                                'update_time' => '2019-10-17T11:50:35Z',
                                'links' => [
                                    0 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/sale/9FY68813RG278922J',
                                        'rel' => 'self',
                                        'method' => 'GET',
                                    ],
                                    1 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/sale/9FY68813RG278922J/refund',
                                        'rel' => 'refund',
                                        'method' => 'POST',
                                    ],
                                    2 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAYID-LWSWIBI77389724VP530350F',
                                        'rel' => 'parent_payment',
                                        'method' => 'GET',
                                    ],
                                ],
                            ],
                        ],
                        1 => [
                            'refund' => [
                                'id' => '42V50763MY2351538',
                                'state' => 'completed',
                                'amount' => [
                                    'total' => '-20.00',
                                    'currency' => 'EUR',
                                ],
                                'parent_payment' => 'PAYID-LWSWIBI77389724VP530350F',
                                'sale_id' => '9FY68813RG278922J',
                                'create_time' => '2019-10-17T11:50:35Z',
                                'update_time' => '2019-10-17T11:50:35Z',
                                'links' => [
                                    0 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/refund/42V50763MY2351538',
                                        'rel' => 'self',
                                        'method' => 'GET',
                                    ],
                                    1 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAYID-LWSWIBI77389724VP530350F',
                                        'rel' => 'parent_payment',
                                        'method' => 'GET',
                                    ],
                                    2 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/sale/9FY68813RG278922J',
                                        'rel' => 'sale',
                                        'method' => 'GET',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'create_time' => '2019-10-15T06:15:33Z',
            'update_time' => '2019-10-17T11:50:35Z',
            'links' => [
                0 => [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAYID-LWSWIBI77389724VP530350F',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
            ],
        ];
    }
}
