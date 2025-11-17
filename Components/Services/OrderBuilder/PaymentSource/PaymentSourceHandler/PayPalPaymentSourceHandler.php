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
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayPal;
use UnexpectedValueException;

class PayPalPaymentSourceHandler extends AbstractPaymentSourceHandler
{
    const SUPPORTED_PAYMENT_TYPES = [
        PaymentType::PAYPAL_CLASSIC_V2,
        PaymentType::PAYPAL_PAY_LATER,
        PaymentType::PAYPAL_EXPRESS_V2,
        PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2,
    ];

    /**
     * {@inheritDoc}
     */
    public function supports($paymentType)
    {
        return \in_array($paymentType, self::SUPPORTED_PAYMENT_TYPES, true);
    }

    /**
     * {@inheritDoc}
     */
    public function createPaymentSource(PayPalOrderParameter $orderParameter)
    {
        $payPalPaymentSourceValue = $this->paymentSourceValueFactory->createPaymentSourceValue($orderParameter);

        if (!$payPalPaymentSourceValue instanceof PayPal) {
            throw new UnexpectedValueException(
                \sprintf(
                    'Payment source PayPal expected. Got "%s"',
                    \gettype($payPalPaymentSourceValue)
                )
            );
        }

        $paymentSource = new PaymentSource();
        $paymentSource->setPaypal($payPalPaymentSourceValue);

        return $paymentSource;
    }
}
