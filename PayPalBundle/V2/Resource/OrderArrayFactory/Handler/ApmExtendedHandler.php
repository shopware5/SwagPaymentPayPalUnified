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

class ApmExtendedHandler extends ApmDefaultHandler
{
    const SUPPORTET_PAYMENT_TYPES = [
        PaymentType::APM_P24,
    ];

    /**
     * {@inheritDoc}
     */
    public function supports($paymentType)
    {
        return \in_array($paymentType, self::SUPPORTET_PAYMENT_TYPES);
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Order $order, $paymentType)
    {
        $getter = 'get' . ucfirst($paymentType);

        $array = parent::handle($order, $paymentType);

        $array['payment_source'][$paymentType]['email'] = $order->getPaymentSource()->$getter()->getEmail();

        return $array;
    }
}
