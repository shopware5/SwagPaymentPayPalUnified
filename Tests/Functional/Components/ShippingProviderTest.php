<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\ShippingProvider;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class ShippingProviderTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testGetWorkingShipping()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/shipping.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $orderProvider = $this->geShippingProvider();
        $shipping = $orderProvider->getCarrierByShippingId(500);

        static::assertEquals('DHL', $shipping);
    }

    /**
     * @return void
     */
    public function testGetShippingWithNullCarrier()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/shipping.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $orderProvider = $this->geShippingProvider();
        static::assertNull($orderProvider->getCarrierByShippingId(600));
    }

    /**
     * @return void
     */
    public function testGetShippingWithEmptyStringAsCarrier()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/shipping.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $orderProvider = $this->geShippingProvider();
        static::assertNull($orderProvider->getCarrierByShippingId(600));
    }

    /**
     * @return ShippingProvider
     */
    private function geShippingProvider()
    {
        return $this->getContainer()->get('paypal_unified.shipping_provider');
    }
}
