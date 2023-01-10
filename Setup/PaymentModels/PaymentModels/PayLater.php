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
use SwagPaymentPayPalUnified\Setup\Assets\Translations;

class PayLater extends AbstractPaymentModel
{
    /**
     * {@inheritDoc}
     */
    public function create()
    {
        $payment = new Payment();
        $payment->setActive(false);
        $payment->setPosition(self::POSITION_PAY_LATER);
        $payment->setName(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME);
        $payment->setDescription(Translations::CONFIG_PAYMENT_TRANSLATIONS['de_DE'][PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME]['description']);
        $payment->setAdditionalDescription(Translations::CONFIG_PAYMENT_TRANSLATIONS['de_DE'][PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME]['additionalDescription']);
        $payment->setAction(self::ACTION_PAYPAL_CLASSIC);
        $payment->setPlugin($this->plugin);

        return $payment;
    }
}
