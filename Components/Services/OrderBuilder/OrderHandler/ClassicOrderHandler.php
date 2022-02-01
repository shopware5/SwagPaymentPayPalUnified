<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler;

use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

class ClassicOrderHandler extends AbstractOrderHandler
{
    const SUPPORTED_PAYMENT_TYPES = [
        PaymentType::PAYPAL_CLASSIC_V2,
        PaymentType::PAYPAL_EXPRESS_V2,
        PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2,
    ];

    public function supports($paymentType)
    {
        return \in_array($paymentType, self::SUPPORTED_PAYMENT_TYPES);
    }

    public function createOrder(PayPalOrderParameter $orderParameter)
    {
        $order = new Order();

        $order->setIntent($this->getIntent());
        $order->setPurchaseUnits($this->createPurchaseUnits($orderParameter));
        $order->setPayer($this->createPayer($orderParameter));
        $order->setApplicationContext($this->createApplicationContext($orderParameter));

        return $order;
    }
}
