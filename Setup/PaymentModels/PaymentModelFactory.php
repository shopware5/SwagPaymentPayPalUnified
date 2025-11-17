<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\PaymentModels;

use InvalidArgumentException;
use Shopware\Models\Plugin\Plugin;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\AbstractPaymentModel;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Bancontact;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Blik;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Eps;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Giropay;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Ideal;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\MultiBanco;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\MyBank;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\PayLater;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\PayPalAdvancedCreditAndDebitCard;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\PayPalClassic;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\PayPalPayUponInvoice;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Przelewy24;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Sepa;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Sofort;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Trustly;

class PaymentModelFactory
{
    /**
     * @var array<AbstractPaymentModel>
     */
    private $paymentModels;

    public function __construct(Plugin $plugin)
    {
        $this->paymentModels = [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME => new PayPalClassic($plugin),
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME => new PayLater($plugin),
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME => new PayPalPayUponInvoice($plugin),
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME => new PayPalAdvancedCreditAndDebitCard($plugin),
            PaymentMethodProviderInterface::BANCONTACT_METHOD_NAME => new Bancontact($plugin),
            PaymentMethodProviderInterface::BLIK_METHOD_NAME => new Blik($plugin),
            PaymentMethodProviderInterface::EPS_METHOD_NAME => new Eps($plugin),
            PaymentMethodProviderInterface::GIROPAY_METHOD_NAME => new Giropay($plugin),
            PaymentMethodProviderInterface::IDEAL_METHOD_NAME => new Ideal($plugin),
            PaymentMethodProviderInterface::MY_BANK_METHOD_NAME => new MyBank($plugin),
            PaymentMethodProviderInterface::P24_METHOD_NAME => new Przelewy24($plugin),
            PaymentMethodProviderInterface::SOFORT_METHOD_NAME => new Sofort($plugin),
            PaymentMethodProviderInterface::MULTIBANCO_METHOD_NAME => new MultiBanco($plugin),
            PaymentMethodProviderInterface::TRUSTLY_METHOD_NAME => new Trustly($plugin),
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME => new Sepa($plugin),
        ];
    }

    /**
     * @param string $name
     *
     * @return AbstractPaymentModel
     */
    public function getPaymentModel($name)
    {
        if (!isset($this->paymentModels[$name])) {
            throw new InvalidArgumentException(\sprintf('The payment method %s was not found.', $name));
        }

        return $this->paymentModels[$name];
    }
}
