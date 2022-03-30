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

abstract class AbstractPaymentSourceHandler
{
    /**
     * @var PaymentSourceValueFactory
     */
    protected $paymentSourceValueFactory;

    public function __construct(PaymentSourceValueFactory $paymentSourceValueFactory)
    {
        $this->paymentSourceValueFactory = $paymentSourceValueFactory;
    }

    /**
     * @param string $paymentType
     *
     * @return bool
     */
    abstract public function supports($paymentType);

    /**
     * @return PaymentSource
     */
    abstract public function createPaymentSource(PayPalOrderParameter $orderParameter);
}
