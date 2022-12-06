<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\ExpressCheckout;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Front;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware_Components_Config as ShopwareConfig;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\Services\ExpressCheckout\CustomerService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use Symfony\Component\Form\FormFactoryInterface;

class CustomerServiceGetSalutationTest extends TestCase
{
    use ReflectionHelperTrait;

    /**
     * @return void
     */
    public function testGetSalutationShouldReturnMrBecauseConfigIsNull()
    {
        $shopwareConfigMock = $this->createMock(ShopwareConfig::class);
        $shopwareConfigMock->method('get')->willReturn(null);

        $customerService = $this->createCustomerService($shopwareConfigMock);
        $reflectionMethod = $this->getReflectionMethod(CustomerService::class, 'getSalutation');

        static::assertSame('mr', $reflectionMethod->invoke($customerService));
    }

    /**
     * @return void
     */
    public function testGetSalutationShouldReturnMrBecauseConfigIsEmpty()
    {
        $shopwareConfigMock = $this->createMock(ShopwareConfig::class);
        $shopwareConfigMock->method('get')->willReturn('');

        $customerService = $this->createCustomerService($shopwareConfigMock);
        $reflectionMethod = $this->getReflectionMethod(CustomerService::class, 'getSalutation');

        static::assertSame('mr', $reflectionMethod->invoke($customerService));
    }

    /**
     * @return void
     */
    public function testGetSalutationShouldReturnNotDefined()
    {
        $shopwareConfigMock = $this->createMock(ShopwareConfig::class);
        $shopwareConfigMock->method('get')->willReturn('mr,mrs,not_defined');

        $customerService = $this->createCustomerService($shopwareConfigMock);
        $reflectionMethod = $this->getReflectionMethod(CustomerService::class, 'getSalutation');

        static::assertSame('not_defined', $reflectionMethod->invoke($customerService));
    }

    /**
     * @return void
     */
    public function testGetSalutationShouldReturnMr()
    {
        $shopwareConfigMock = $this->createMock(ShopwareConfig::class);
        $shopwareConfigMock->method('get')->willReturn('mr,mrs');

        $customerService = $this->createCustomerService($shopwareConfigMock);
        $reflectionMethod = $this->getReflectionMethod(CustomerService::class, 'getSalutation');

        static::assertSame('mr', $reflectionMethod->invoke($customerService));
    }

    /**
     * @return void
     */
    public function testGetSalutationShouldReturnAnotherSalutation()
    {
        $shopwareConfigMock = $this->createMock(ShopwareConfig::class);
        $shopwareConfigMock->method('get')->willReturn('anotherSalutation,mr,mrs');

        $customerService = $this->createCustomerService($shopwareConfigMock);
        $reflectionMethod = $this->getReflectionMethod(CustomerService::class, 'getSalutation');

        static::assertSame('anotherSalutation', $reflectionMethod->invoke($customerService));
    }

    /**
     * @return CustomerService
     */
    private function createCustomerService(ShopwareConfig $shopwareConfig)
    {
        return new CustomerService(
            $shopwareConfig,
            $this->createMock(Connection::class),
            $this->createMock(FormFactoryInterface::class),
            $this->createMock(ContextServiceInterface::class),
            $this->createMock(RegisterServiceInterface::class),
            $this->createMock(Enlight_Controller_Front::class),
            $this->createMock(DependencyProvider::class),
            $this->createMock(PaymentMethodProviderInterface::class),
            $this->createMock(LoggerServiceInterface::class)
        );
    }
}
