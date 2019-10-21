<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks\ResultSet;

class GetSale
{
    /**
     * @return array
     */
    public static function get()
    {
        return [
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
        ];
    }
}
