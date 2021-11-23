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

    const PAYPAL_CLASSIC_V2 = 'PayPalClassicV2';
    const PAYPAL_PLUS_V2 = 'PayPalPlusV2';
    const PAYPAL_INVOICE_V2 = 'PayPalPlusInvoiceV2';
    const PAYPAL_PAY_UPON_INVOICE_V2 = 'PayPalPayUponInvoiceV2';
    const PAYPAL_EXPRESS_V2 = 'PayPalExpressV2';
    const PAYPAL_SMART_PAYMENT_BUTTONS_V2 = 'PayPalSmartPaymentButtonsV2';

    private function __construct()
    {
    }
}
