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
    const PAYPAL_PAY_UPON_INVOICE_V2 = 'PayPalPayUponInvoiceV2';
    const PAYPAL_EXPRESS_V2 = 'PayPalExpressV2';
    const PAYPAL_SMART_PAYMENT_BUTTONS_V2 = 'PayPalSmartPaymentButtonsV2';

    const APM_BANCONTACT = 'bancontact';
    const APM_BLIK = 'blik';
    const APM_EPS = 'eps';
    const APM_GIROPAY = 'giropay';
    const APM_IDEAL = 'ideal';
    const APM_MULTIBANCO = 'multibanco';
    const APM_MYBANK = 'mybank';
    const APM_OXXO = 'oxxo';
    const APM_P24 = 'p24';
    const APM_SOFORT = 'sofort';
    const APM_TRUSTLY = 'trustly';

    private function __construct()
    {
    }
}
