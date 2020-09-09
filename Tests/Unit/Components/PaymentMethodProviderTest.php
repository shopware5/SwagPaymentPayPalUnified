<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;

class PaymentMethodProviderTest extends TestCase
{
    public function test_get_payment_method()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());

        static::assertNotNull($provider->getPaymentMethodModel(), 'The payment method should not be null');
    }

    public function test_set_payment_inactive()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());
        $provider->setPaymentMethodActiveFlag(false);

        $payment = $provider->getPaymentMethodModel();
        static::assertFalse($payment->getActive());
    }

    public function test_set_payment_active()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());
        $provider->setPaymentMethodActiveFlag(true);

        $payment = $provider->getPaymentMethodModel();
        static::assertTrue($payment->getActive());
    }

    public function test_get_payment_id()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());
        $paymentIdQuery = 'SELECT pm.id FROM s_core_paymentmeans pm WHERE pm.name=:name';

        /** @var Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');

        $paymentId = (int) $connection->executeQuery(
            $paymentIdQuery,
            [':name' => PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME]
        )->fetchColumn();

        static::assertSame($paymentId, $provider->getPaymentId(Shopware()->Container()->get('dbal_connection')));
    }

    public function test_get_payment_active()
    {
        $activeFlag = (new PaymentMethodProvider(Shopware()->Models()))->getPaymentMethodActiveFlag(
            Shopware()->Container()->get('dbal_connection')
        );

        static::assertTrue($activeFlag);
    }
}
