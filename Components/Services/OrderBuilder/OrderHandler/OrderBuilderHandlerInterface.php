<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler;

use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

interface OrderBuilderHandlerInterface
{
    /**
     * @param string $paymentType
     *
     * @return bool
     */
    public function supports($paymentType);

    /**
     * @return Order
     */
    public function createOrder(PayPalOrderParameter $orderParameter);
}
