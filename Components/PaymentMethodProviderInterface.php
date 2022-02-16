<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

use Shopware\Models\Payment\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

interface PaymentMethodProviderInterface
{
    /**
     * The technical names of the unified payment methods.
     */
    const PAYPAL_UNIFIED_PAYMENT_METHOD_NAME = 'SwagPaymentPayPalUnified';

    const PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME = 'SwagPaymentPayPalUnifiedPayUponInvoice';

    const PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME = 'SwagPaymentPayPalUnifiedAdvancedCreditDebitCard';

    const BANCONTACT_METHOD_NAME = 'SwagPaymentPayPalUnifiedBancontact';

    const BLIK_METHOD_NAME = 'SwagPaymentPayPalUnifiedBlik';

    const EPS_METHOD_NAME = 'SwagPaymentPayPalUnifiedEps';

    const GIROPAY_METHOD_NAME = 'SwagPaymentPayPalUnifiedGiropay';

    const IDEAL_METHOD_NAME = 'SwagPaymentPayPalUnifiedIdeal';

    const MULTIBANCO_METHOD_NAME = 'SwagPaymentPayPalUnifiedMultibanco';

    const MY_BANK_METHOD_NAME = 'SwagPaymentPayPalUnifiedMyBank';

    const OXXO_METHOD_NAME = 'SwagPaymentPayPalUnifiedOXXO';

    const P24_METHOD_NAME = 'SwagPaymentPayPalUnifiedP24';

    const SOFORT_METHOD_NAME = 'SwagPaymentPayPalUnifiedSofort';

    const TRUSTLY_METHOD_NAME = 'SwagPaymentPayPalUnifiedTrustly';

    const PAYPAL_UNIFIED_INSTALLMENTS_METHOD_NAME = 'SwagPaymentPayPalUnifiedInstallments';

    /**
     * @param string $paymentMethodName
     *
     * @return Payment|null
     *
     * @see PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME or PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME
     */
    public function getPaymentMethodModel($paymentMethodName);

    /**
     * @param bool   $active
     * @param string $paymentMethodName
     *
     * @see PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME or PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME
     */
    public function setPaymentMethodActiveFlag($paymentMethodName, $active);

    /**
     * @param string $paymentMethodName
     *
     * @return bool
     *
     * @see PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME or PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME
     */
    public function getPaymentMethodActiveFlag($paymentMethodName);

    /**
     * @param string $paymentMethodName
     *
     * @return int
     *
     * @see PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME or PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME
     */
    public function getPaymentId($paymentMethodName);

    /**
     * @param array<string> $paymentMethodNames
     *
     * @return array<string, int>
     */
    public function getActivePayments(array $paymentMethodNames);

    /**
     * @param array<string> $paymentMethodNames
     *
     * @return array<string, int>
     */
    public function getPayments(array $paymentMethodNames);

    /**
     * @return array<string>
     */
    public function getPayPalMethodNames();

    /**
     * @return array<string>
     */
    public function getAlternativePaymentMethodNames();

    /**
     * @return array<string>
     */
    public function getAllUnifiedNames();

    /**
     * @param string $paymentMethodName
     *
     * @return PaymentType::*
     *
     * @see PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME or PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME
     */
    public function getPaymentTypeByName($paymentMethodName);
}
