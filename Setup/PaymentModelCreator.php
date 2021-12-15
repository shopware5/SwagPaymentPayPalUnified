<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup;

use Shopware\Models\Payment\Payment;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;

class PaymentModelCreator
{
    const CLASSIC_PAYMENT_POSITION = -100;
    const PAY_UPON_INVOICE_PAYMENT_POSITION = self::CLASSIC_PAYMENT_POSITION + 1;

    /**
     * @param string $paymentMethodName
     *
     * @return Payment
     */
    public function createModel($paymentMethodName)
    {
        if ($paymentMethodName === PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME) {
            return $this->createClassicModel();
        }

        if ($paymentMethodName === PaymentMethodProvider::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME) {
            return $this->createInvoiceModel();
        }

        throw new \InvalidArgumentException(sprintf('Payment method with name %s not found.', $paymentMethodName));
    }

    private function createClassicModel()
    {
        $payment = new Payment();
        $payment->setActive(false);
        $payment->setPosition(self::CLASSIC_PAYMENT_POSITION);
        $payment->setName(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        $payment->setDescription('PayPal');
        $payment->setAdditionalDescription($this->getUnifiedPaymentLogo() . 'Bezahlung per PayPal - einfach, schnell und sicher.');
        $payment->setAction('PaypalUnifiedV2');

        return $payment;
    }

    private function createInvoiceModel()
    {
        $payment = new Payment();
        $payment->setActive(false);
        $payment->setPosition(self::PAY_UPON_INVOICE_PAYMENT_POSITION);
        $payment->setName(PaymentMethodProvider::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME);
        $payment->setDescription('PayPal pay upon invoice');
        $payment->setAdditionalDescription($this->getUnifiedPaymentLogo() . 'Wir brauchen sicher einen neuen Text :p'); // TODO: (PT-12531) determine which snippet we need here
        $payment->setAction('PaypalUnifiedV2PayUponInvoice');

        return $payment;
    }

    /**
     * @return string
     */
    private function getUnifiedPaymentLogo()
    {
        return '<!-- PayPal Logo -->'
            . '<a href="https://www.paypal.com/de/cgi-bin/webscr?cmd=xpt/cps/popup/OLCWhatIsPayPal-outside" target="_blank" rel="noopener">'
            . '<img src="{link file=\'frontend/_public/src/img/sidebar-paypal-generic.png\' fullPath}" alt="Logo \'PayPal empfohlen\'">'
            . '</a><br><!-- PayPal Logo -->';
    }
}
