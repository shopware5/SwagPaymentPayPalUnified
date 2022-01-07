<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Backend;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\Backend\ShopRegistrationService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class ShopRegistrationServiceTest extends TestCase
{
    /**
     * @beforeClass
     */
    public function skipTestIfShopRegistrationServiceInterfaceDoesNotExist()
    {
        if (!class_exists(ShopRegistrationServiceInterface::class)) {
            static::markTestSkipped(sprintf('Skipping %s, as %s does not exist.', self::class, 'Shopware\Components\ShopRegistrationServiceInterface'));
        }
    }

    public function testInstantiate()
    {
        $registrationService = $this->getShopRegistrationService();

        static::assertInstanceOf(ShopRegistrationService::class, $registrationService);
    }

    public function testThrowsInvalidArgumentException()
    {
        static::expectException(\InvalidArgumentException::class);

        $this->getShopRegistrationService()->registerShopById('9d253e94-457c-4943-aa3c-03304d1a8347');
    }

    /**
     * @dataProvider defaultIsUsedWhenNoShopIsFoundDataProvider
     *
     * @param bool $shopExists
     */
    public function testDefaultIsUsedWhenNoShopIsFound($shopExists)
    {
        $shopRepositoryMock = static::createMock(Repository::class);

        $shopRepositoryMock->method('getActiveById')
            ->willReturn($shopExists ? static::createMock(Shop::class) : null);

        $shopRepositoryMock->method('getActiveDefault')
            ->willReturn(static::createMock(Shop::class));

        $shopRepositoryMock->expects($shopExists ? static::never() : static::once())
            ->method('getActiveDefault');

        $registrationService = $this->getShopRegistrationService(
            static::createConfiguredMock(ModelManager::class, [
                'getRepository' => $shopRepositoryMock,
            ])
        );

        $registrationService->registerShopById(0);
    }

    /**
     * @dataProvider registerResourcesIsCalledDataProvider
     */
    public function testRegisterResourcesIsCalled($registrationServiceProvided)
    {
        $shopRepositoryMock = static::createMock(Repository::class);
        $shopMock = static::createMock(Shop::class);
        $registrationServiceMock = static::createMock(ShopRegistrationServiceInterface::class);

        $shopMock->expects($registrationServiceProvided ? static::never() : static::once())
            ->method('registerResources');

        $shopRepositoryMock->method('getActiveById')
            ->willReturn($shopMock);

        $registrationServiceMock->expects($registrationServiceProvided ? static::once() : static::never())
            ->method('registerResources')
            ->with($shopMock);

        $registrationService = $this->getShopRegistrationService(
            static::createConfiguredMock(ModelManager::class, [
                'getRepository' => $shopRepositoryMock,
            ]),
            null,
            $registrationServiceProvided ? $registrationServiceMock : null
        );

        $registrationService->registerShopById(0);
    }

    public function defaultIsUsedWhenNoShopIsFoundDataProvider()
    {
        return [
            [
                'Shop exists' => true,
            ],
            [
                'Shop does not exist' => false,
            ],
        ];
    }

    public function registerResourcesIsCalledDataProvider()
    {
        return [
            [
                'Registration service is provided' => true,
            ],
            [
                'Registration service is not provided' => false,
            ],
        ];
    }

    private function getShopRegistrationService(
        $modelManager = null,
        $settingsService = null,
        $registrationService = null
    ) {
        return new ShopRegistrationService(
            $modelManager instanceof ModelManager ? $modelManager : static::createMock(ModelManager::class),
            $settingsService instanceof SettingsServiceInterface ? $settingsService : static::createMock(SettingsServiceInterface::class),
            $registrationService
        );
    }
}
