<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle;

final class BaseURL
{
    const SANDBOX = 'https://api.sandbox.paypal.com/';
    const LIVE = 'https://api.paypal.com/';

    private function __construct()
    {
    }
}
