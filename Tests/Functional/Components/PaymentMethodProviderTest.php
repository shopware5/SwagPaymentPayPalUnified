<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;

class PaymentMethodProviderTest extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testGetPaymentNameByIdShouldReturnAPaymentName()
    {
        $paymentMethodProvider = $this->getContainer()->get('paypal_unified.payment_method_provider');
        static::assertInstanceOf(PaymentMethodProvider::class, $paymentMethodProvider);

        $result = $paymentMethodProvider->getPaymentNameById(7);

        static::assertSame('SwagPaymentPayPalUnified', $result);
    }

    /**
     * @return void
     */
    public function testGetPaymentNameByIdShouldReturnNull()
    {
        $paymentMethodProvider = $this->getContainer()->get('paypal_unified.payment_method_provider');
        static::assertInstanceOf(PaymentMethodProvider::class, $paymentMethodProvider);

        $result = $paymentMethodProvider->getPaymentNameById(999);

        static::assertNull($result);
    }
}
