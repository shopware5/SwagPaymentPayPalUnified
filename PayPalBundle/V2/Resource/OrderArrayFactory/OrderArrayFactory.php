<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderArrayFactory;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use UnexpectedValueException;

class OrderArrayFactory
{
    /**
     * @var array<OrderToArrayHandlerInterface>
     */
    private $orderToArrayHandler = [];

    public function addHandler(OrderToArrayHandlerInterface $orderToArrayHandler)
    {
        $this->orderToArrayHandler[] = $orderToArrayHandler;
    }

    /**
     * @param string $paymentType
     *
     * @return array<string, mixed>
     */
    public function toArray(Order $order, $paymentType)
    {
        foreach ($this->orderToArrayHandler as $orderToArrayHandler) {
            if (!$orderToArrayHandler->supports($paymentType)) {
                continue;
            }

            return $orderToArrayHandler->handle($order, $paymentType);
        }

        throw new UnexpectedValueException(
            sprintf('OrderToArrayHandler handler for payment type "%s" not found', $paymentType)
        );
    }
}
