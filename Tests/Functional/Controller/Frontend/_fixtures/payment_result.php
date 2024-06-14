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
    'state' => 'created',
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
                ],
            ],
            'payee' => [
                'merchant_id' => 'D7RFFDVUU6F7N',
                'email' => 'merchant-de@shopware.com',
            ],
            'item_list' => [
                'items' => [
                    0 => [
                        'name' => 'Cigar Special 40%',
                        'sku' => 'SW10006',
                        'price' => '35.95',
                        'currency' => 'EUR',
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
            ],
        ],
    ],
    'redirect_urls' => [
        'return_url' => 'http://shopware.localhost/PaypalUnified/return/plus/1/basketId/plus/swPaymentToken/R2gq0X3SYTrx6WXopcXb9B03MtYKdlYb?paymentId=PAYID-MJF55OY30P44731CY226773H',
        'cancel_url' => 'http://shopware.localhost/PaypalUnified/cancel/swPaymentToken/R2gq0X3SYTrx6WXopcXb9B03MtYKdlYb',
    ],
    'create_time' => '2022-04-05T06:16:26Z',
    'update_time' => '2022-04-05T06:16:49Z',
    'links' => [
        0 => [
            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAYID-MJF55OY30P44731CY226773H',
            'rel' => 'self',
            'method' => 'GET',
        ],
        1 => [
            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAYID-MJF55OY30P44731CY226773H/execute',
            'rel' => 'execute',
            'method' => 'POST',
        ],
        2 => [
            'href' => 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-1G126719HS857043U',
            'rel' => 'approval_url',
            'method' => 'REDIRECT',
        ],
    ],
];
