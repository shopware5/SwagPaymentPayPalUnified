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
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card;
use UnexpectedValueException;

class AdvancedCreditDebitCardPaymentSourceHandler extends AbstractPaymentSourceHandler
{
    /**
     * {@inheritDoc}
     */
    public function supports($paymentType)
    {
        return $paymentType === PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD;
    }

    /**
     * {@inheritDoc}
     */
    public function createPaymentSource(PayPalOrderParameter $orderParameter)
    {
        $payPalPaymentSourceValue = $this->paymentSourceValueFactory->createPaymentSourceValue($orderParameter);

        if (!$payPalPaymentSourceValue instanceof Card) {
            throw new UnexpectedValueException(
                \sprintf(
                    'Payment source Card expected. Got "%s"',
                    \gettype($payPalPaymentSourceValue)
                )
            );
        }

        $paymentSource = new PaymentSource();
        $paymentSource->setCard($payPalPaymentSourceValue);

        return $paymentSource;
    }
}
