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
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\P24;

class P24PaymentSourceValueHandler extends AbstractPaymentSourceValueHandler
{
    public function supports($paymentType)
    {
        return $paymentType === PaymentType::APM_P24;
    }

    /**
     * {@inheritDoc}
     */
    public function createPaymentSourceValue(PayPalOrderParameter $orderParameter)
    {
        $paymentSourceValue = new P24();

        $this->setDefaultValues($paymentSourceValue, $orderParameter);
        $this->setValues($paymentSourceValue, $orderParameter);

        return $paymentSourceValue;
    }

    public function setValues(P24 $paymentSourceValue, PayPalOrderParameter $orderParameter)
    {
        $paymentSourceValue->setEmail($orderParameter->getCustomer()['additional']['user']['email']);
    }
}
