<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks\ResultSet;

class CreatePaymentSale
{
    /**
     * @return array
     */
    public static function get()
    {
        return [
            'id' => 'PAY-9HW62735H82101921LLK3D4I',
            'intent' => 'sale',
            'state' => 'created',
            'payer' => [
                'payment_method' => 'paypal',
            ],
            'transactions' => [
                0 => [
                    'amount' => [
                        'total' => '301.15',
                        'currency' => 'EUR',
                        'details' => [
                            'subtotal' => '297.25',
                            'tax' => '0.00',
                            'shipping' => '3.90',
                        ],
                    ],
                    'item_list' => [
                        'items' => [
                            0 => [
                                'name' => 'Strandtuch "Ibiza"',
                                'sku' => 'SW10178',
                                'price' => '19.95',
                                'currency' => 'EUR',
                                'quantity' => 15,
                            ],
                            1 => [
                                'name' => 'Warenkorbrabatt',
                                'sku' => 'SHIPPINGDISCOUNT',
                                'price' => '-2.00',
                                'currency' => 'EUR',
                                'quantity' => 1,
                            ],
                        ],
                    ],
                    'related_resources' => [],
                ],
            ],
            'create_time' => '2018-04-17T08:36:01Z',
            'links' => [
                0 => [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-9HW62735H82101921LLK3D4I',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
                1 => [
                    'href' => 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-49W9096312907153R',
                    'rel' => 'approval_url',
                    'method' => 'REDIRECT',
                ],
                2 => [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-9HW62735H82101921LLK3D4I/execute',
                    'rel' => 'execute',
                    'method' => 'POST',
                ],
            ],
        ];
    }
}
