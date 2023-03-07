<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueHandler;

use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayPal;

class PayPalPaymentSourceValueHandler extends AbstractPaymentSourceValueHandler
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
    public function createPaymentSourceValue(PayPalOrderParameter $orderParameter)
    {
        $paymentSourceValue = new PayPal();
        $paymentSourceValue->setExperienceContext($this->createExperienceContext($orderParameter));

        return $paymentSourceValue;
    }
}
