<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Components;

/**
 * No complete table names can be declared below to avoid references to the actual plugin.
 */
final class SettingsTable
{
    const GENERAL = 'general';
    const EXPRESS_CHECKOUT = 'express';
    const INSTALLMENTS = 'installments';
    const PLUS = 'plus';
    const PAY_UPON_INVOICE = 'pay_upon_invoice';

    const FULL = [
        self::GENERAL => 'swag_payment_paypal_unified_settings_general',
        self::EXPRESS_CHECKOUT => 'swag_payment_paypal_unified_settings_express',
        self::INSTALLMENTS => 'swag_payment_paypal_unified_settings_installments',
        self::PLUS => 'swag_payment_paypal_unified_settings_plus',
        self::PAY_UPON_INVOICE => 'swag_payment_paypal_unified_settings_pay_upon_invoice',
    ];

    private function __construct()
    {
    }
}
