<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\OrderBuilder\OrderHandler;

use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler\AbstractOrderHandler;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\ApplicationContext;

class OrderHandlerMock extends AbstractOrderHandler
{
    /**
     * {@inheritDoc}
     */
    public function supports($paymentType)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function createOrder(PayPalOrderParameter $orderParameter)
    {
        return new Order();
    }

    /**
     * @return ApplicationContext
     */
    public function createApplicationContextWrapper(PayPalOrderParameter $orderParameter)
    {
        return $this->createApplicationContext($orderParameter);
    }
}
