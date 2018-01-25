<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'id' => 'PAY-7VM569522T960852JLCX7VEY',
    'intent' => 'authorize',
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
                                    'authorization' => [
                                            'id' => '5AC42815Y31405904',
                                            'state' => 'captured',
                                            'amount' => [
                                                    'total' => '45.94',
                                                    'currency' => 'EUR',
                                                    'details' => [
                                                            'subtotal' => '19.95',
                                                            'shipping' => '25.99',
                                                        ],
                                                ],
                                            'payment_mode' => 'INSTANT_TRANSFER',
                                            'protection_eligibility' => 'ELIGIBLE',
                                            'protection_eligibility_type' => 'ITEM_NOT_RECEIVED_ELIGIBLE,UNAUTHORIZED_PAYMENT_ELIGIBLE',
                                            'parent_payment' => 'PAY-7VM569522T960852JLCX7VEY',
                                            'valid_until' => '2017-03-25T08:19:36Z',
                                            'create_time' => '2017-02-24T09:19:36Z',
                                            'update_time' => '2017-02-24T13:16:31Z',
                                            'links' => [
                                                    0 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/authorization/5AC42815Y31405904',
                                                            'rel' => 'self',
                                                            'method' => 'GET',
                                                        ],
                                                    1 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/authorization/5AC42815Y31405904/capture',
                                                            'rel' => 'capture',
                                                            'method' => 'POST',
                                                        ],
                                                    2 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/authorization/5AC42815Y31405904/void',
                                                            'rel' => 'void',
                                                            'method' => 'POST',
                                                        ],
                                                    3 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/authorization/5AC42815Y31405904/reauthorize',
                                                            'rel' => 'reauthorize',
                                                            'method' => 'POST',
                                                        ],
                                                    4 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-7VM569522T960852JLCX7VEY',
                                                            'rel' => 'parent_payment',
                                                            'method' => 'GET',
                                                        ],
                                                ],
                                        ],
                                ],
                            1 => [
                                    'capture' => [
                                            'id' => '2R757000DJ996203U',
                                            'amount' => [
                                                    'total' => '45.94',
                                                    'currency' => 'EUR',
                                                ],
                                            'state' => 'partially_refunded',
                                            'transaction_fee' => [
                                                    'value' => '1.22',
                                                    'currency' => 'EUR',
                                                ],
                                            'parent_payment' => 'PAY-7VM569522T960852JLCX7VEY',
                                            'create_time' => '2017-02-24T13:16:31Z',
                                            'links' => [
                                                    0 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/2R757000DJ996203U',
                                                            'rel' => 'self',
                                                            'method' => 'GET',
                                                        ],
                                                    1 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/2R757000DJ996203U/refund',
                                                            'rel' => 'refund',
                                                            'method' => 'POST',
                                                        ],
                                                    2 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/authorization/5AC42815Y31405904',
                                                            'rel' => 'authorization',
                                                            'method' => 'GET',
                                                        ],
                                                    3 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-7VM569522T960852JLCX7VEY',
                                                            'rel' => 'parent_payment',
                                                            'method' => 'GET',
                                                        ],
                                                ],
                                        ],
                                ],
                            2 => [
                                    'refund' => [
                                            'id' => '2CC97062EN862510G',
                                            'state' => 'completed',
                                            'amount' => [
                                                    'total' => '5.00',
                                                    'currency' => 'EUR',
                                                ],
                                            'parent_payment' => 'PAY-7VM569522T960852JLCX7VEY',
                                            'capture_id' => '2R757000DJ996203U',
                                            'create_time' => '2017-02-27T08:59:07Z',
                                            'update_time' => '2017-02-27T08:59:07Z',
                                            'links' => [
                                                    0 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/refund/2CC97062EN862510G',
                                                            'rel' => 'self',
                                                            'method' => 'GET',
                                                        ],
                                                    1 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-7VM569522T960852JLCX7VEY',
                                                            'rel' => 'parent_payment',
                                                            'method' => 'GET',
                                                        ],
                                                    2 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/2R757000DJ996203U',
                                                            'rel' => 'capture',
                                                            'method' => 'GET',
                                                        ],
                                                ],
                                        ],
                                ],
                            3 => [
                                    'refund' => [
                                            'id' => '9XY10005FS386951C',
                                            'state' => 'completed',
                                            'amount' => [
                                                    'total' => '3.00',
                                                    'currency' => 'EUR',
                                                ],
                                            'parent_payment' => 'PAY-7VM569522T960852JLCX7VEY',
                                            'capture_id' => '2R757000DJ996203U',
                                            'create_time' => '2017-02-27T09:16:06Z',
                                            'update_time' => '2017-02-27T09:16:06Z',
                                            'links' => [
                                                    0 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/refund/9XY10005FS386951C',
                                                            'rel' => 'self',
                                                            'method' => 'GET',
                                                        ],
                                                    1 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-7VM569522T960852JLCX7VEY',
                                                            'rel' => 'parent_payment',
                                                            'method' => 'GET',
                                                        ],
                                                    2 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/2R757000DJ996203U',
                                                            'rel' => 'capture',
                                                            'method' => 'GET',
                                                        ],
                                                ],
                                        ],
                                ],
                            4 => [
                                    'refund' => [
                                            'id' => '2XF359842E3925412',
                                            'state' => 'completed',
                                            'amount' => [
                                                    'total' => '3.00',
                                                    'currency' => 'EUR',
                                                ],
                                            'parent_payment' => 'PAY-7VM569522T960852JLCX7VEY',
                                            'capture_id' => '2R757000DJ996203U',
                                            'create_time' => '2017-02-27T09:37:31Z',
                                            'update_time' => '2017-02-27T09:37:31Z',
                                            'links' => [
                                                    0 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/refund/2XF359842E3925412',
                                                            'rel' => 'self',
                                                            'method' => 'GET',
                                                        ],
                                                    1 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-7VM569522T960852JLCX7VEY',
                                                            'rel' => 'parent_payment',
                                                            'method' => 'GET',
                                                        ],
                                                    2 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/2R757000DJ996203U',
                                                            'rel' => 'capture',
                                                            'method' => 'GET',
                                                        ],
                                                ],
                                        ],
                                ],
                            5 => [
                                    'refund' => [
                                            'id' => '5K455256092187229',
                                            'state' => 'completed',
                                            'amount' => [
                                                    'total' => '1.00',
                                                    'currency' => 'EUR',
                                                ],
                                            'parent_payment' => 'PAY-7VM569522T960852JLCX7VEY',
                                            'capture_id' => '2R757000DJ996203U',
                                            'create_time' => '2017-03-13T08:25:00Z',
                                            'update_time' => '2017-03-13T08:25:00Z',
                                            'links' => [
                                                    0 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/refund/5K455256092187229',
                                                            'rel' => 'self',
                                                            'method' => 'GET',
                                                        ],
                                                    1 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-7VM569522T960852JLCX7VEY',
                                                            'rel' => 'parent_payment',
                                                            'method' => 'GET',
                                                        ],
                                                    2 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/2R757000DJ996203U',
                                                            'rel' => 'capture',
                                                            'method' => 'GET',
                                                        ],
                                                ],
                                        ],
                                ],
                            6 => [
                                    'refund' => [
                                            'id' => '9C358025FN047330N',
                                            'state' => 'completed',
                                            'amount' => [
                                                    'total' => '1.00',
                                                    'currency' => 'EUR',
                                                ],
                                            'parent_payment' => 'PAY-7VM569522T960852JLCX7VEY',
                                            'capture_id' => '2R757000DJ996203U',
                                            'create_time' => '2017-03-13T08:26:16Z',
                                            'update_time' => '2017-03-13T08:26:16Z',
                                            'links' => [
                                                    0 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/refund/9C358025FN047330N',
                                                            'rel' => 'self',
                                                            'method' => 'GET',
                                                        ],
                                                    1 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-7VM569522T960852JLCX7VEY',
                                                            'rel' => 'parent_payment',
                                                            'method' => 'GET',
                                                        ],
                                                    2 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/2R757000DJ996203U',
                                                            'rel' => 'capture',
                                                            'method' => 'GET',
                                                        ],
                                                ],
                                        ],
                                ],
                            7 => [
                                    'refund' => [
                                            'id' => '6PF02585YC663253B',
                                            'state' => 'completed',
                                            'amount' => [
                                                    'total' => '12.00',
                                                    'currency' => 'EUR',
                                                ],
                                            'parent_payment' => 'PAY-7VM569522T960852JLCX7VEY',
                                            'capture_id' => '2R757000DJ996203U',
                                            'create_time' => '2017-03-13T09:48:50Z',
                                            'update_time' => '2017-03-13T09:48:50Z',
                                            'links' => [
                                                    0 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/refund/6PF02585YC663253B',
                                                            'rel' => 'self',
                                                            'method' => 'GET',
                                                        ],
                                                    1 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-7VM569522T960852JLCX7VEY',
                                                            'rel' => 'parent_payment',
                                                            'method' => 'GET',
                                                        ],
                                                    2 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/2R757000DJ996203U',
                                                            'rel' => 'capture',
                                                            'method' => 'GET',
                                                        ],
                                                ],
                                        ],
                                ],
                            8 => [
                                    'refund' => [
                                            'id' => '59Y4920397981342N',
                                            'state' => 'completed',
                                            'amount' => [
                                                    'total' => '14.00',
                                                    'currency' => 'EUR',
                                                ],
                                            'parent_payment' => 'PAY-7VM569522T960852JLCX7VEY',
                                            'capture_id' => '2R757000DJ996203U',
                                            'create_time' => '2017-03-14T15:42:09Z',
                                            'update_time' => '2017-03-14T15:42:09Z',
                                            'links' => [
                                                    0 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/refund/59Y4920397981342N',
                                                            'rel' => 'self',
                                                            'method' => 'GET',
                                                        ],
                                                    1 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-7VM569522T960852JLCX7VEY',
                                                            'rel' => 'parent_payment',
                                                            'method' => 'GET',
                                                        ],
                                                    2 => [
                                                            'href' => 'https://api.sandbox.paypal.com/v1/payments/capture/2R757000DJ996203U',
                                                            'rel' => 'capture',
                                                            'method' => 'GET',
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                ],
        ],
    'create_time' => '2017-03-14T15:55:13Z',
    'update_time' => '2017-03-14T15:42:09Z',
    'links' => [
            0 => [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-7VM569522T960852JLCX7VEY',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
        ],
];
