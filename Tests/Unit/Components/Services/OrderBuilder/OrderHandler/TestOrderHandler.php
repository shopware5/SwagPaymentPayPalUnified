<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Services\OrderBuilder\OrderHandler;

use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler\AbstractOrderHandler;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

class TestOrderHandler extends AbstractOrderHandler
{
    /**
     * {@inheritDoc}
     */
    public function createPurchaseUnits(PayPalOrderParameter $orderParameter)
    {
        return parent::createPurchaseUnits($orderParameter);
    }

    public function supports($paymentType)
    {
        return true;
    }

    public function createOrder(PayPalOrderParameter $orderParameter)
    {
        return new Order();
    }
}
