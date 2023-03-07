<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components;

use Enlight_Controller_Router;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopware\Components\DependencyInjection\Container;
use stdClass;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class DependencyProviderGetRouterTest extends TestCase
{
    use ShopRegistrationTrait;
    use ContainerTrait;

    /**
     * @return void
     */
    public function testGetRouter()
    {
        $container = $this->getContainer()->get('service_container');
        static::assertInstanceOf(Container::class, $container);

        $result = $this->createDependencyProvider($container)->getRouter();

        static::assertInstanceOf(Enlight_Controller_Router::class, $result);
    }

    /**
     * @return void
     */
    public function testGetRouterIsNotInitializedInTheContainer()
    {
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('initialized')->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Router is not initialized');

        $this->createDependencyProvider($containerMock)->getRouter();
    }

    /**
     * @return void
     */
    public function testGetRouterIsNotInstanceOfExpectedRouter()
    {
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('initialized')->willReturn(true);
        $containerMock->method('get')->willReturn(new stdClass());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Router expect to be instance of Enlight_Controller_Router got object');

        $this->createDependencyProvider($containerMock)->getRouter();
    }

    /**
     * @return DependencyProvider
     */
    private function createDependencyProvider(Container $container)
    {
        return new DependencyProvider($container);
    }
}
