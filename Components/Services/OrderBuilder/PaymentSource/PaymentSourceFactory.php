<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource;

use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use UnexpectedValueException;

class PaymentSourceFactory
{
    /**
     * @var array<AbstractPaymentSourceHandler>
     */
    private $paymentSourceHandler = [];

    public function addHandler(AbstractPaymentSourceHandler $purchaseUnitHandler)
    {
        $this->paymentSourceHandler[] = $purchaseUnitHandler;
    }

    /**
     * @return PaymentSource
     */
    public function createPaymentSource(PayPalOrderParameter $orderParameter)
    {
        $paymentSourceHandler = $this->getPaymentSourceHandler($orderParameter->getPaymentType());

        return $paymentSourceHandler->createPaymentSource($orderParameter);
    }

    /**
     * @param string $paymentType
     *
     * @return AbstractPaymentSourceHandler
     */
    private function getPaymentSourceHandler($paymentType)
    {
        foreach ($this->paymentSourceHandler as $paymentSourceHandler) {
            if (!$paymentSourceHandler->supports($paymentType)) {
                continue;
            }

            return $paymentSourceHandler;
        }

        throw new UnexpectedValueException(
            sprintf('Payment source handler for payment type %s not found', $paymentType)
        );
    }
}
