<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels;

use Shopware\Models\Payment\Payment;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;

class PayPalClassic implements PaymentModelInterface
{
    /**
     * {@inheritDoc}
     */
    public function create()
    {
        $payment = new Payment();
        $payment->setActive(true);
        $payment->setPosition(self::POSITION_PAYPAL_CLASSIC);
        $payment->setName(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        $payment->setDescription('PayPal');
        $payment->setAdditionalDescription($this->getUnifiedPaymentLogo() . 'Bezahlung per PayPal - einfach, schnell und sicher.');
        $payment->setAction(self::ACTION_PAYPAL_CLASSIC);

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
