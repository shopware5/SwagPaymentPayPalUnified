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

class ApmDefaultHandler implements OrderToArrayHandlerInterface
{
    const SUPPORTET_PAYMENT_TYPES = [
        PaymentType::APM_BANCONTACT,
        PaymentType::APM_BLIK,
        PaymentType::APM_EPS,
        PaymentType::APM_GIROPAY,
        PaymentType::APM_IDEAL,
        PaymentType::APM_MULTIBANCO,
        PaymentType::APM_MYBANK,
        PaymentType::APM_SOFORT,
        PaymentType::APM_TRUSTLY,
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

        return [
            'intent' => $order->getIntent(),
            'processing_instruction' => $order->getProcessingInstruction(),
            'purchase_units' => [
                [
                    'reference_id' => $order->getPurchaseUnits()[0]->getReferenceId(),
                    'amount' => [
                        'currency_code' => $order->getPurchaseUnits()[0]->getAmount()->getCurrencyCode(),
                        'value' => $order->getPurchaseUnits()[0]->getAmount()->getValue(),
                    ],
                ],
            ],
            'application_context' => [
                'locale' => $order->getApplicationContext()->getLocale(),
                'return_url' => $order->getApplicationContext()->getReturnUrl(),
                'cancel_url' => $order->getApplicationContext()->getCancelUrl(),
            ],
            'payment_source' => [
                $paymentType => [
                    'country_code' => $order->getPaymentSource()->$getter()->getCountryCode(),
                    'name' => $order->getPaymentSource()->$getter()->getName(),
                ],
            ],
        ];
    }
}
