<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components;

use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Shop\DetachedShop;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class DependencyProviderTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;

    public function testServiceAvailable()
    {
        static::assertSame(DependencyProvider::class, \get_class($this->getContainer()->get('paypal_unified.dependency_provider')));
    }

    public function testCanBeConstructed()
    {
        $dp = new DependencyProvider($this->getContainer());

        static::assertNotNull($dp);
    }

    public function testGetShopReturnShop()
    {
        $dp = new DependencyProvider($this->getContainer());

        static::assertInstanceOf(DetachedShop::class, $dp->getShop());
    }

    public function testGetShopReturnNull()
    {
        $dp = new DependencyProvider(new ContainerMockWithNoShop());
        $shop = $dp->getShop();

        static::assertNull($shop);
    }

    public function testGetModuleHasModule()
    {
        $dp = new DependencyProvider($this->getContainer());

        static::assertNotNull($dp->getModule('basket'));
    }
}

class ContainerMockWithNoShop extends Container
{
    public function has($name)
    {
        return false;
    }
}
