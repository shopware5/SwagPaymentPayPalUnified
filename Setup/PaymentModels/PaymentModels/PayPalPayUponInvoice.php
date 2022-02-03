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

class PayPalPayUponInvoice extends AbstractPaymentModel
{
    /**
     * {@inheritDoc}
     */
    public function create()
    {
        $payment = new Payment();
        $payment->setActive(false);
        $payment->setPosition(self::POSITION_PAY_UPON_INVOICE);
        $payment->setName(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME);
        $payment->setDescription('PayPal pay upon invoice');
        $payment->setAdditionalDescription('Wir brauchen einen neuen Text'); // TODO: (PT-12567) determine which snippet we need here
        $payment->setAction(self::ACTION_PAYPAL_PAY_UPON_INVOICE);
        $payment->setPlugin($this->plugin);

        return $payment;
    }
}
