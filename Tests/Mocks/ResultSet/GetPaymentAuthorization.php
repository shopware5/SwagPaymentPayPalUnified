<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks\ResultSet;

class GetPaymentAuthorization
{
    /**
     * @return array
     */
    public static function get()
    {
        return [
            'id' => 'PAYID-LWUCR3I8X859573C8797034F',
            'intent' => 'authorize',
            'state' => 'approved',
            'cart' => '80158336VS2663934',
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
                            'authorization' => [
                                'id' => '6TD7833611940173R',
                                'state' => 'voided',
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
                                'parent_payment' => 'PAYID-LWUCR3I8X859573C8797034F',
                                'valid_until' => '2019-11-15T09:41:49Z',
                                'create_time' => '2019-10-17T08:41:49Z',
                                'update_time' => '2019-10-17T08:42:58Z',
                                'links' => [
                                    0 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/authorization/6TD7833611940173R',
                                        'rel' => 'self',
                                        'method' => 'GET',
                                    ],
                                    1 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/authorization/6TD7833611940173R/capture',
                                        'rel' => 'capture',
                                        'method' => 'POST',
                                    ],
                                    2 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/authorization/6TD7833611940173R/void',
                                        'rel' => 'void',
                                        'method' => 'POST',
                                    ],
                                    3 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/authorization/6TD7833611940173R/reauthorize',
                                        'rel' => 'reauthorize',
                                        'method' => 'POST',
                                    ],
                                    4 => [
                                        'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAYID-LWUCR3I8X859573C8797034F',
                                        'rel' => 'parent_payment',
                                        'method' => 'GET',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'create_time' => '2019-10-17T08:40:13Z',
            'update_time' => '2019-10-17T08:42:58Z',
            'links' => [
                0 => [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAYID-LWUCR3I8X859573C8797034F',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
            ],
        ];
    }
}
