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

class DependencyProviderTest extends TestCase
{
    public function testServiceAvailable()
    {
        static::assertSame(DependencyProvider::class, \get_class(Shopware()->Container()->get('paypal_unified.dependency_provider')));
    }

    public function testCanBeConstructed()
    {
        $dp = new DependencyProvider(Shopware()->Container());

        static::assertNotNull($dp);
    }

    public function testGetShopReturnShop()
    {
        $dp = new DependencyProvider(Shopware()->Container());

        static::assertSame(DetachedShop::class, \get_class($dp->getShop()));
    }

    public function testGetShopReturnNull()
    {
        $dp = new DependencyProvider(new ContainerMockWithNoShop());

        static::assertNull($dp->getShop());
    }

    public function testGetModuleHasModule()
    {
        $dp = new DependencyProvider(Shopware()->Container());

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
