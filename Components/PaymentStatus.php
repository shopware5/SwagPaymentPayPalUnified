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
     * The default status for cancelled orders
     */
    const ORDER_STATUS_CLARIFICATION_REQUIRED = 8;
    /**
     * The default status for approved orders
     */
    const PAYMENT_STATUS_PARTIALLY_PAID = 11;
    /**
     * The default status for approved orders
     */
    const PAYMENT_STATUS_PAID = 12;
    /**
     * @deprecated Deprecated since 2.5.0, will be removed in 3.0.0, use PAYMENT_STATUS_PAID instead
     */
    const PAYMENT_STATUS_APPROVED = self::PAYMENT_STATUS_PAID;
    /**
     * The default status for open orders
     */
    const PAYMENT_STATUS_OPEN = 17;
    /**
     * The default status for refunded orders
     */
    const PAYMENT_STATUS_REFUNDED = 20;
    /**
     * The default status for voided orders
     */
    const PAYMENT_STATUS_CANCELLED = 35;
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
