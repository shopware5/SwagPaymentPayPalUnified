<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderBuilder;

use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler\OrderBuilderHandlerInterface;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use UnexpectedValueException;

class OrderFactory
{
    /**
     * @var array<OrderBuilderHandlerInterface>
     */
    private $orderHandler = [];

    public function addHandler(OrderBuilderHandlerInterface $orderHandler)
    {
        $this->orderHandler[] = $orderHandler;
    }

    /**
     * @return Order
     */
    public function createOrder(PayPalOrderParameter $orderParameter)
    {
        foreach ($this->orderHandler as $orderHandler) {
            if (!$orderHandler->supports($orderParameter->getPaymentType())) {
                continue;
            }

            return $orderHandler->createOrder($orderParameter);
        }

        throw new UnexpectedValueException(
            \sprintf('Create order handler for payment type "%s" not found', $orderParameter->getPaymentType())
        );
    }
}
