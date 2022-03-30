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
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Oxxo;

class OxxoPaymentSourceValueHandler extends AbstractPaymentSourceValueHandler
{
    public function supports($paymentType)
    {
        return $paymentType === PaymentType::APM_OXXO;
    }

    /**
     * {@inheritDoc}
     */
    public function createPaymentSourceValue(PayPalOrderParameter $orderParameter)
    {
        $paymentSourceValue = new Oxxo();

        $this->setDefaultValues($paymentSourceValue, $orderParameter);
        $this->setValues($paymentSourceValue, $orderParameter);

        return $paymentSourceValue;
    }

    public function setValues(Oxxo $paymentSourceValue, PayPalOrderParameter $orderParameter)
    {
        $paymentSourceValue->setEmail($orderParameter->getCustomer()['additional']['user']['email']);
    }
}
