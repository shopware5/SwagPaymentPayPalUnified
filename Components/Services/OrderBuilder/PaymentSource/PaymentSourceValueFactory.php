<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource;

use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueHandler\AbstractPaymentSourceValueHandler;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\AbstractPaymentSource;
use UnexpectedValueException;

class PaymentSourceValueFactory
{
    /**
     * @var array<AbstractPaymentSourceValueHandler>
     */
    private $paymentSourceValueHandler = [];

    /**
     * @return AbstractPaymentSource
     */
    public function createPaymentSourceValue(PayPalOrderParameter $orderParameter)
    {
        $paymentSourceValueHandler = $this->getPaymentSourceValueHandler($orderParameter->getPaymentType());

        return $paymentSourceValueHandler->createPaymentSourceValue($orderParameter);
    }

    public function addHandler(AbstractPaymentSourceValueHandler $paymentSourceValueHandler)
    {
        $this->paymentSourceValueHandler[] = $paymentSourceValueHandler;
    }

    /**
     * @param string $paymentType
     *
     * @return AbstractPaymentSourceValueHandler
     */
    private function getPaymentSourceValueHandler($paymentType)
    {
        foreach ($this->paymentSourceValueHandler as $paymentSourceValueHandler) {
            if ($paymentSourceValueHandler->supports($paymentType)) {
                return $paymentSourceValueHandler;
            }
        }

        throw new UnexpectedValueException(
            \sprintf('Payment source value handler for payment type %s not found', $paymentType)
        );
    }
}
