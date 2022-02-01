<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\PaymentModels;

use InvalidArgumentException;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Bancontact;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Blik;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Eps;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Giropay;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Ideal;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\MultiBanco;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\MyBank;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Oxxo;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\PaymentModelInterface;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\PayPalClassic;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\PayPalPayUponInvoice;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Przelewy24;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Sofort;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModels\Trustly;

class PaymentModelFactory
{
    /**
     * @var array<PaymentModelInterface>
     */
    private $paymentModels;

    public function __construct()
    {
        $this->paymentModels = [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME => new PayPalClassic(),
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME => new PayPalPayUponInvoice(),
            PaymentMethodProviderInterface::BANCONTACT_METHOD_NAME => new Bancontact(),
            PaymentMethodProviderInterface::BLIK_METHOD_NAME => new Blik(),
            PaymentMethodProviderInterface::EPS_METHOD_NAME => new Eps(),
            PaymentMethodProviderInterface::GIROPAY_METHOD_NAME => new Giropay(),
            PaymentMethodProviderInterface::IDEAL_METHOD_NAME => new Ideal(),
            PaymentMethodProviderInterface::MY_BANK_METHOD_NAME => new MyBank(),
            PaymentMethodProviderInterface::P24_METHOD_NAME => new Przelewy24(),
            PaymentMethodProviderInterface::SOFORT_METHOD_NAME => new Sofort(),
            PaymentMethodProviderInterface::MULTIBANCO_METHOD_NAME => new MultiBanco(),
            PaymentMethodProviderInterface::OXXO_METHOD_NAME => new Oxxo(),
            PaymentMethodProviderInterface::TRUSTLY_METHOD_NAME => new Trustly(),
        ];
    }

    /**
     * @param string $name
     *
     * @return PaymentModelInterface
     */
    public function getPaymentModel($name)
    {
        if (!isset($this->paymentModels[$name])) {
            throw new InvalidArgumentException(sprintf('The payment method %s was not found.', $name));
        }

        return $this->paymentModels[$name];
    }
}
