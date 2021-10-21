<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle;

final class PaymentType
{
    const PAYPAL_CLASSIC = 'PayPalClassic';
    const PAYPAL_PLUS = 'PayPalPlus';
    const PAYPAL_INVOICE = 'PayPalPlusInvoice';
    const PAYPAL_EXPRESS = 'PayPalExpress';
    const PAYPAL_SMART_PAYMENT_BUTTONS = 'PayPalSmartPaymentButtons';

    const PAYPAL_V2 = 'PayPalV2';

    private function __construct()
    {
    }
}
