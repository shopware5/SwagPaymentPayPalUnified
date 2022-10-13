<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components;

use PHPUnit\Framework\MockObject\MockObject;
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

    /**
     * @return void
     */
    public function testServiceAvailable()
    {
        static::assertInstanceOf(
            DependencyProvider::class,
            $this->getContainer()->get('paypal_unified.dependency_provider')
        );
    }

    /**
     * @return void
     */
    public function testCanBeConstructed()
    {
        $dp = new DependencyProvider($this->getContainer());

        static::assertNotNull($dp);
    }

    /**
     * @return void
     */
    public function testGetShopReturnShop()
    {
        $dp = new DependencyProvider($this->getContainer());

        static::assertInstanceOf(DetachedShop::class, $dp->getShop());
    }

    /**
     * @return void
     */
    public function testGetShopReturnNull()
    {
        $dp = new DependencyProvider(new ContainerMockWithNoShop());
        $shop = $dp->getShop();

        static::assertNull($shop);
    }

    /**
     * @return void
     */
    public function testGetModuleHasModule()
    {
        $dp = new DependencyProvider($this->getContainer());

        static::assertNotNull($dp->getModule('basket'));
    }

    /**
     * @return void
     */
    public function testIsInitialized()
    {
        $dependencyProvider = new DependencyProvider($this->createContainerMock(true));
        static::assertTrue($dependencyProvider->isInitialized('anyService'));

        $dependencyProvider = new DependencyProvider($this->createContainerMock(false));
        static::assertFalse($dependencyProvider->isInitialized('anyService'));
    }

    /**
     * @param bool $initializedReturnValue
     *
     * @return Container&MockObject
     */
    private function createContainerMock($initializedReturnValue)
    {
        $containerMock = $this->createMock(Container::class);
        $containerMock->expects(static::once())
            ->method('initialized')
            ->willReturn($initializedReturnValue);

        return $containerMock;
    }
}

class ContainerMockWithNoShop extends Container
{
    public function has($name)
    {
        return false;
    }
}
