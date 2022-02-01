<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueHandler;

use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\AbstractApmPaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\AbstractPaymentSource;

abstract class AbstractPaymentSourceValueHandler
{
    const NAME_TEMPLATE = '%s %s';

    /**
     * @return AbstractPaymentSource
     */
    abstract public function createPaymentSourceValue(PayPalOrderParameter $orderParameter);

    /**
     * @param string $paymentType
     *
     * @return bool
     */
    abstract public function supports($paymentType);

    protected function setDefaultValues(AbstractApmPaymentSource $apmPaymentSourceValue, PayPalOrderParameter $orderParameter)
    {
        $customer = $orderParameter->getCustomer();

        $apmPaymentSourceValue->setName(
            $this->createName(
                $customer['additional']['user']['firstname'],
                $customer['additional']['user']['lastname']
            )
        );

        $apmPaymentSourceValue->setCountryCode($customer['additional']['country']['countryiso']);
    }

    /**
     * @param string $firstname
     * @param string $lastname
     *
     * @return string
     */
    private function createName($firstname, $lastname)
    {
        return sprintf(self::NAME_TEMPLATE, $firstname, $lastname);
    }
}
