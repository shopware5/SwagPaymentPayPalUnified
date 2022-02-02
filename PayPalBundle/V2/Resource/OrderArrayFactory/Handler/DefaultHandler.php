<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderArrayFactory\Handler;

use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderArrayFactory\OrderToArrayHandlerInterface;

class DefaultHandler implements OrderToArrayHandlerInterface
{
    const SUPPORTET_PAYMENT_TYPES = [
        PaymentType::PAYPAL_CLASSIC_V2,
        PaymentType::PAYPAL_EXPRESS_V2,
        PaymentType::PAYPAL_PAY_UPON_INVOICE_V2,
        PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2,
    ];

    public function supports($paymentType)
    {
        return \in_array($paymentType, self::SUPPORTET_PAYMENT_TYPES);
    }

    public function handle(Order $order, $paymentType)
    {
        return $order->toArray();
    }
}
