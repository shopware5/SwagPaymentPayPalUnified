<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\_fixtures;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;

class SimplePayPalOrderCreator
{
    /**
     * @return Order
     */
    public function createSimplePayPalOrder()
    {
        $capture = new Capture();
        $capture->setStatus(PaymentStatusV2::ORDER_CAPTURE_COMPLETED);

        $payments = new Payments();
        $payments->setCaptures([$capture]);

        $amount = new Amount();
        $amount->setValue('100');

        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setPayments($payments);
        $purchaseUnit->setAmount($amount);

        $order = new Order();
        $order->setId('123456789');
        $order->setIntent(PaymentIntentV2::CAPTURE);
        $order->setPurchaseUnits([$purchaseUnit]);

        return $order;
    }
}
