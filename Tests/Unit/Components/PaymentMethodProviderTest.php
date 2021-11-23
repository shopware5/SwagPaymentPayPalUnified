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

class PaymentMethodProviderTest extends TestCase
{
    public function testGetPaymentMethod()
    {
        $provider = $this->getPaymentMethodProvider();

        static::assertNotNull($provider->getPaymentMethodModel(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME), 'The payment method should not be null');
    }

    public function testSetPaymentInactive()
    {
        $provider = $this->getPaymentMethodProvider();
        $provider->setPaymentMethodActiveFlag(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);

        $payment = $provider->getPaymentMethodModel(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        static::assertInstanceOf(Payment::class, $payment);
        static::assertFalse($payment->getActive());
    }

    public function testSetPaymentActive()
    {
        $provider = $this->getPaymentMethodProvider();
        $provider->setPaymentMethodActiveFlag(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);

        $payment = $provider->getPaymentMethodModel(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        static::assertInstanceOf(Payment::class, $payment);
        static::assertTrue($payment->getActive());
    }

    public function testGetPaymentId()
    {
        $provider = $this->getPaymentMethodProvider();
        $paymentIdQuery = 'SELECT pm.id FROM s_core_paymentmeans pm WHERE pm.name=:name';

        $connection = Shopware()->Container()->get('dbal_connection');

        $paymentId = (int) $connection->executeQuery(
            $paymentIdQuery,
            [':name' => PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME]
        )->fetchColumn();

        static::assertSame($paymentId, $provider->getPaymentId(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME));
    }

    public function testGetPaymentActive()
    {
        $activeFlag = $this->getPaymentMethodProvider()->getPaymentMethodActiveFlag(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        static::assertTrue($activeFlag);
    }

    private function getPaymentMethodProvider()
    {
        return new PaymentMethodProvider(
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Models()
        );
    }
}
