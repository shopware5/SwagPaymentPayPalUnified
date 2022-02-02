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

class PuiOrderHandler extends AbstractOrderHandler
{
    public function supports($paymentType)
    {
        return $paymentType === PaymentType::PAYPAL_PAY_UPON_INVOICE_V2;
    }

    public function createOrder(PayPalOrderParameter $orderParameter)
    {
        $order = new Order();

        $order->setIntent($this->getIntent());
        $order->setPurchaseUnits($this->createPurchaseUnits($orderParameter));
        $order->setPayer($this->createPayer($orderParameter));
        $order->setPaymentSource($this->createPaymentSource($orderParameter, $order));

        return $order;
    }
}
