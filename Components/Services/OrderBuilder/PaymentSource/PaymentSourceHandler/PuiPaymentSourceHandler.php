<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceHandler;

use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\AbstractPaymentSourceHandler;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;
use UnexpectedValueException;

class PuiPaymentSourceHandler extends AbstractPaymentSourceHandler
{
    /**
     * {@inheritDoc}
     */
    public function supports($paymentType)
    {
        return $paymentType === PaymentType::PAYPAL_PAY_UPON_INVOICE_V2;
    }

    /**
     * {@inheritDoc}
     */
    public function createPaymentSource(PayPalOrderParameter $orderParameter)
    {
        $paymentSourceValue = $this->paymentSourceValueFactory->createPaymentSourceValue($orderParameter);

        if (!$paymentSourceValue instanceof PayUponInvoice) {
            throw new UnexpectedValueException(
                sprintf(
                    'Payment source PayUponInvoice expected. Got "%s"',
                    \gettype($paymentSourceValue)
                )
            );
        }

        $paymentSource = new PaymentSource();
        $paymentSource->setPayUponInvoice($paymentSourceValue);

        return $paymentSource;
    }
}
