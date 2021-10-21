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

    private function __construct()
    {
    }
}
