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

class PayPalAdvancedCreditAndDebitCard extends AbstractPaymentModel
{
    /**
     * {@inheritDoc}
     */
    public function create()
    {
        $payment = new Payment();
        $payment->setActive(false);
        $payment->setPosition(self::POSITION_ADVANCED_CEDIT_DEBIT_CARD);
        $payment->setName(PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME);
        $payment->setDescription(Translations::CONFIG_PAYMENT_TRANSLATIONS['de_DE'][PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME]['description']);
        $payment->setAdditionalDescription(Translations::CONFIG_PAYMENT_TRANSLATIONS['de_DE'][PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME]['additionalDescription']);
        $payment->setAction(self::ACTION_PAYPAL_ADVANCED_CREDIT_DEBIT_CARD);
        $payment->setPlugin($this->plugin);

        return $payment;
    }
}
