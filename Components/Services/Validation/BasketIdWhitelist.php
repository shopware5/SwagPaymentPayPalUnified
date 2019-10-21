<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Validation;

final class BasketIdWhitelist
{
    /**
     * Add a value here to always use the simple validator whenever a basket is being validated.
     * This is important, because not all payments may generate a basket unique id as seen in a regular paypal payment.
     */
    const WHITELIST_IDS = [
        'PayPalExpress' => 'express',
        'PayPalPlus' => 'plus',
    ];

    private function __construct()
    {
    }
}
