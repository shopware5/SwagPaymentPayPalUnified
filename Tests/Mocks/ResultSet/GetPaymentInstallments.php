<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks\ResultSet;

class GetPaymentInstallments
{
    /**
     * @return array
     */
    public static function get()
    {
        return [
            'credit_financing_offered' => [
                'total_cost' => [
                    'value' => '486.57',
                    'currency' => 'EUR',
                ],
                'term' => 6,
                'monthly_payment' => [
                    'value' => '81.22',
                    'currency' => 'EUR',
                ],
                'total_interest' => [
                    'value' => '12.57',
                    'currency' => 'EUR',
                ],
                'payer_acceptance' => true,
                'cart_amount_immutable' => true,
            ],
        ];
    }
}
