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
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Eps;

class EpsPaymentSourceValueHandler extends AbstractPaymentSourceValueHandler
{
    /**
     * {@inheritDoc}
     */
    public function supports($paymentType)
    {
        return $paymentType === PaymentType::APM_EPS;
    }

    /**
     * {@inheritDoc}
     */
    public function createPaymentSourceValue(PayPalOrderParameter $orderParameter)
    {
        $paymentSourceValue = new Eps();

        $this->setDefaultValues($paymentSourceValue, $orderParameter);
        $paymentSourceValue->setExperienceContext($this->createExperienceContext($orderParameter));

        return $paymentSourceValue;
    }
}
