<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels;

use Shopware\Models\Payment\Payment;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;

class Oxxo implements PaymentModelInterface
{
    /**
     * {@inheritDoc}
     */
    public function create()
    {
        $payment = new Payment();
        $payment->setActive(true);
        $payment->setPosition(self::POSITION_OXXO);
        $payment->setName(PaymentMethodProviderInterface::OXXO_METHOD_NAME);
        $payment->setDescription('OXXO');
        $payment->setAdditionalDescription($this->getDescription());
        $payment->setAction(self::ACTION_PAYPAL_APM);

        return $payment;
    }

    /**
     * @return string
     */
    private function getDescription()
    {
        $descriptionArray = [
            '<img src="https://www.paypalobjects.com/images/checkout/alternative_payments/paypal_oxxo_color.svg" alt="Logo OXXO">',
        ];

        return implode(' ', $descriptionArray);
    }
}
