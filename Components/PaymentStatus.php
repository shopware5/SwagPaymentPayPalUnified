<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

final class PaymentStatus
{
    /**
     * The default status from PayPal to identify completed transactions
     */
    const PAYMENT_COMPLETED = 'completed';
    /**
     * The default state from PayPal to identify voided transactions (order/authorization)
     */
    const PAYMENT_VOIDED = 'voided';

    private function __construct()
    {
    }
}
