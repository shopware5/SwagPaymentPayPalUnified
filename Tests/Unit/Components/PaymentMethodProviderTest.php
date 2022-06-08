<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components;

use PHPUnit\Framework\TestCase;
use Shopware\Models\Payment\Payment;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use UnexpectedValueException;

class PaymentMethodProviderTest extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testGetPaymentMethod()
    {
        $provider = $this->getPaymentMethodProvider();

        static::assertNotNull($provider->getPaymentMethodModel(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME), 'The payment method should not be null');
    }

    /**
     * @return void
     */
    public function testSetPaymentInactive()
    {
        $provider = $this->getPaymentMethodProvider();
        $provider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);

        $payment = $provider->getPaymentMethodModel(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        static::assertInstanceOf(Payment::class, $payment);
        static::assertFalse($payment->getActive());
    }

    /**
     * @return void
     */
    public function testSetPaymentActive()
    {
        $provider = $this->getPaymentMethodProvider();
        $provider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);

        $payment = $provider->getPaymentMethodModel(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        static::assertInstanceOf(Payment::class, $payment);
        static::assertTrue($payment->getActive());
    }

    /**
     * @return void
     */
    public function testGetPaymentId()
    {
        $provider = $this->getPaymentMethodProvider();
        $paymentIdQuery = 'SELECT pm.id FROM s_core_paymentmeans pm WHERE pm.name=:name';

        $connection = $this->getContainer()->get('dbal_connection');

        $paymentId = (int) $connection->executeQuery(
            $paymentIdQuery,
            [':name' => PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME]
        )->fetchColumn();

        static::assertSame($paymentId, $provider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME));
    }

    /**
     * @return void
     */
    public function testGetPaymentActive()
    {
        $activeFlag = $this->getPaymentMethodProvider()->getPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        static::assertTrue($activeFlag);
    }

    /**
     * @return void
     */
    public function testGetPaymentInactive()
    {
        $provider = $this->getPaymentMethodProvider();
        $provider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::BLIK_METHOD_NAME, false);
        $activeFlag = $provider->getPaymentMethodActiveFlag(PaymentMethodProviderInterface::BLIK_METHOD_NAME);

        static::assertFalse($activeFlag);
    }

    /**
     * @return void
     */
    public function testGetPaymentTypeByNameThrowsException()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Payment type for payment method "DoesNotExists" not found');
        $this->getPaymentMethodProvider()->getPaymentTypeByName('DoesNotExists');
    }

    /**
     * @dataProvider getPaymentTypeByNameProvider
     *
     * @param string $paymentMethodName
     * @param string $expectedPaymentType
     *
     * @return void
     */
    public function testGetPaymentTypeByName($paymentMethodName, $expectedPaymentType)
    {
        static::assertSame($expectedPaymentType, $this->getPaymentMethodProvider()->getPaymentTypeByName($paymentMethodName));
    }

    /**
     * @return array<array{0: PaymentMethodProviderInterface::*, 1: PaymentType::*}>
     */
    public function getPaymentTypeByNameProvider()
    {
        return [
            [PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, PaymentType::PAYPAL_CLASSIC_V2],
            [PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME, PaymentType::PAYPAL_PAY_UPON_INVOICE_V2],
            [PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME, PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD],
            [PaymentMethodProviderInterface::BANCONTACT_METHOD_NAME, PaymentType::APM_BANCONTACT],
            [PaymentMethodProviderInterface::BLIK_METHOD_NAME, PaymentType::APM_BLIK],
            [PaymentMethodProviderInterface::EPS_METHOD_NAME, PaymentType::APM_EPS],
            [PaymentMethodProviderInterface::GIROPAY_METHOD_NAME, PaymentType::APM_GIROPAY],
            [PaymentMethodProviderInterface::IDEAL_METHOD_NAME, PaymentType::APM_IDEAL],
            [PaymentMethodProviderInterface::MULTIBANCO_METHOD_NAME, PaymentType::APM_MULTIBANCO],
            [PaymentMethodProviderInterface::MY_BANK_METHOD_NAME, PaymentType::APM_MYBANK],
            [PaymentMethodProviderInterface::OXXO_METHOD_NAME, PaymentType::APM_OXXO],
            [PaymentMethodProviderInterface::P24_METHOD_NAME, PaymentType::APM_P24],
            [PaymentMethodProviderInterface::SOFORT_METHOD_NAME, PaymentType::APM_SOFORT],
            [PaymentMethodProviderInterface::TRUSTLY_METHOD_NAME, PaymentType::APM_TRUSTLY],
            [PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME, PaymentType::PAYPAL_PAY_LATER],
        ];
    }

    /**
     * @return PaymentMethodProvider
     */
    private function getPaymentMethodProvider()
    {
        return new PaymentMethodProvider(
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('models')
        );
    }
}
