<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderArrayFactory;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

interface OrderToArrayHandlerInterface
{
    /**
     * @param string $paymentType
     *
     * @return bool
     */
    public function supports($paymentType);

    /**
     * @param string $paymentType
     *
     * @return array<string, mixed>
     */
    public function handle(Order $order, $paymentType);
}
