<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\Models\Settings\Installments as InstallmentsSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\Plus as PlusSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class SettingsServiceTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

    const SHOP_ID = 1;
    const CLIENT_ID = 'TEST_CLIENT_ID';
    const CLIENT_SECRET = 'TEST_CLIENT_SECRET';
    const SANDBOX = true;
    const SHOW_SIDEBAR_LOGO = false;
    const PLUS_ACTIVE = true;
    const ACTIVE = true;

    /**
     * @return void
     */
    public function testServiceAvailable()
    {
        static::assertNotNull($this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID)));
    }

    /**
     * @return void
     */
    public function testGetGeneralSettingsByShopId()
    {
        $this->createTestSettings();

        $settingsModel = $this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID))->getSettings(self::SHOP_ID);
        static::assertInstanceOf(General::class, $settingsModel);

        static::assertTrue($settingsModel->getActive());
        static::assertSame(self::CLIENT_ID, $settingsModel->getClientId());
        static::assertSame(self::CLIENT_SECRET, $settingsModel->getClientSecret());
        static::assertTrue($settingsModel->getSandbox());
        static::assertFalse($settingsModel->getShowSidebarLogo());
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $this->createTestSettings();

        $settingsService = $this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID));
        static::assertSame(self::CLIENT_ID, $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_CLIENT_ID));
        static::assertSame(self::CLIENT_SECRET, $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_CLIENT_SECRET));
    }

    /**
     * @return void
     */
    public function testGetWithoutShopThrowsException()
    {
        $this->createTestSettings();
        $settingsService = $this->createSettingsService();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not retrieve a single setting without a shop instance.');
        $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_INTENT);
    }

    /**
     * @return void
     */
    public function testHasSettingsFalse()
    {
        $settingsService = $this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID));

        static::assertFalse($settingsService->hasSettings());
    }

    /**
     * @return void
     */
    public function testHasSettingsFalseBecauseNoShopIsAvailable()
    {
        $settingsService = $this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID));

        static::assertFalse($settingsService->hasSettings());
    }

    /**
     * @return void
     */
    public function testHasSettingsTrue()
    {
        $this->createTestSettings();

        $settingsService = $this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID));

        static::assertTrue($settingsService->hasSettings());
    }

    /**
     * @return void
     */
    public function testGetSettingsInstallments()
    {
        $this->createInstallmentsTestSettings();

        $settingsService = $this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID));

        $installmentsSettings = $settingsService->getSettings(self::SHOP_ID, SettingsTable::INSTALLMENTS);
        static::assertInstanceOf(InstallmentsSettingsModel::class, $installmentsSettings);

        static::assertTrue($installmentsSettings->getAdvertiseInstallments());
    }

    /**
     * @return void
     */
    public function testGetSettingsPlus()
    {
        $this->createPlusTestSettings();

        $settingsService = $this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID));

        $plusSettings = $settingsService->getSettings(self::SHOP_ID, SettingsTable::PLUS);
        static::assertInstanceOf(PlusSettingsModel::class, $plusSettings);

        static::assertTrue($plusSettings->getActive());
        static::assertTrue($plusSettings->getRestyle());
    }

    /**
     * @return void
     */
    public function testHasSettingsInstallments()
    {
        $this->createInstallmentsTestSettings();

        $settingsService = $this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID));

        static::assertTrue($settingsService->hasSettings(SettingsTable::INSTALLMENTS));
    }

    /**
     * @return void
     */
    public function testHasSettingsPlus()
    {
        $this->createPlusTestSettings();

        $settingsService = $this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID));

        static::assertTrue($settingsService->hasSettings(SettingsTable::PLUS));
    }

    /**
     * @return void
     */
    public function testHasSettingsExpress()
    {
        $this->createExpressTestSettings();

        $settingsService = $this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID));

        static::assertTrue($settingsService->hasSettings(SettingsTable::EXPRESS_CHECKOUT));
    }

    /**
     * @return void
     */
    public function testGetExpress()
    {
        $this->createExpressTestSettings();

        $settingsService = $this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID));

        static::assertTrue((bool) $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_CART_ACTIVE, SettingsTable::EXPRESS_CHECKOUT));
        static::assertTrue((bool) $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_DETAIL_ACTIVE, SettingsTable::EXPRESS_CHECKOUT));
        static::assertTrue((bool) $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_LOGIN_ACTIVE, SettingsTable::EXPRESS_CHECKOUT));
        static::assertTrue((bool) $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_OFF_CANVAS_ACTIVE, SettingsTable::EXPRESS_CHECKOUT));
    }

    /**
     * @return void
     */
    public function testGetSettingsReturnsNullWithoutCorrectTable()
    {
        $settingsService = $this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID));

        static::assertNull($settingsService->getSettings(self::SHOP_ID, 'THIS_TABLE_DOES_NOT_EXIST'));
    }

    /**
     * @return void
     */
    public function testGetWillThrowExceptionWithWrongSettingsType()
    {
        $settingsService = $this->createSettingsService($this->createShopMock(self::SHOP_ID, self::SHOP_ID));

        $this->expectException(RuntimeException::class);
        $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_INTENT, 'THIS_TABLE_DOES_NOT_EXIST');
    }

    /**
     * @return void
     */
    public function testSettingsServiceTakesTheCorrectShopActiveShopHasNoSettings()
    {
        $this->insertGeneralSettingsFromArray(['shop_id' => self::SHOP_ID, 'active' => true]);

        $settingsService = $this->createSettingsService($this->createShopMock(2, self::SHOP_ID));
        static::assertTrue($settingsService->hasSettings());

        $settingsService = $this->createSettingsService($this->createShopMock(3, self::SHOP_ID));
        static::assertTrue($settingsService->hasSettings());

        $settingsService = $this->createSettingsService($this->createShopMock(12, self::SHOP_ID));
        static::assertTrue($settingsService->hasSettings());
    }

    /**
     * @return void
     */
    public function testSettingsServiceTakesTheCorrectShopActiveShopHasSettings()
    {
        $activeShopId = 12;
        $this->insertGeneralSettingsFromArray(['shop_id' => $activeShopId, 'active' => true]);

        $settingsService = $this->createSettingsService($this->createShopMock(12, self::SHOP_ID));

        $settingsResult = $settingsService->getSettings();

        static::assertInstanceOf(General::class, $settingsResult);
        static::assertTrue($settingsService->hasSettings());
        static::assertSame($activeShopId, (int) $settingsResult->getShopId());
    }

    /**
     * @return void
     */
    public function testSettingsServiceTakesTheCorrectShopNoShopIsSet()
    {
        $this->insertGeneralSettingsFromArray(['shop_id' => self::SHOP_ID, 'active' => true]);

        $settingsService = $this->createSettingsService();

        static::assertFalse($settingsService->hasSettings());
    }

    /**
     * @return void
     */
    public function testSettingsServiceTakesTheCorrectShopShopIsSetButHasNoSettingsAndNoMainShop()
    {
        $this->insertGeneralSettingsFromArray(['shop_id' => self::SHOP_ID, 'active' => true]);

        $settingsService = $this->createSettingsService($this->createShopMock(12));

        static::assertFalse($settingsService->hasSettings());
    }

    /**
     * @return void
     */
    public function testSettingsServiceTakesTheCorrectShopShopIsSetButHasNoSettingsAndMainShopWithoutId()
    {
        $this->insertGeneralSettingsFromArray(['shop_id' => self::SHOP_ID, 'active' => true]);

        $mainShop = $this->createMock(Shop::class);
        $shop = $this->createMock(Shop::class);
        $shop->method('getId')->willReturn(12);
        $shop->method('getMain')->willReturn($mainShop);

        $settingsService = $this->createSettingsService($shop);

        static::assertFalse($settingsService->hasSettings());
    }

    /**
     * @return SettingsService
     */
    private function createSettingsService(Shop $shop = null)
    {
        $dependencyProviderMock = $this->createMock(DependencyProvider::class);
        $dependencyProviderMock->expects(static::once())->method('getShop')->willReturn($shop);

        return new SettingsService(
            $this->getContainer()->get('models'),
            $dependencyProviderMock
        );
    }

    /**
     * @param int $shopId
     * @param int $mainShopId
     *
     * @return Shop&MockObject
     */
    private function createShopMock($shopId, $mainShopId = null)
    {
        $shop = $this->createMock(Shop::class);
        $shop->method('getId')->willReturn($shopId);

        if (\is_int($mainShopId)) {
            $mainShop = $this->createMock(Shop::class);
            $mainShop->method('getId')->willReturn($mainShopId);

            $shop->method('getMain')->willReturn($mainShop);
        }

        return $shop;
    }

    /**
     * @return void
     */
    private function createTestSettings()
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => self::SHOP_ID,
            'clientId' => self::CLIENT_ID,
            'clientSecret' => self::CLIENT_SECRET,
            'showSidebarLogo' => self::SHOW_SIDEBAR_LOGO,
            'active' => self::ACTIVE,
            'sandbox' => self::SANDBOX,
        ]);
    }

    /**
     * @return void
     */
    private function createInstallmentsTestSettings()
    {
        $this->insertInstallmentsSettingsFromArray([
            'shopId' => self::SHOP_ID,
            'advertiseInstallments' => true,
        ]);
    }

    /**
     * @return void
     */
    private function createPlusTestSettings()
    {
        $this->insertPlusSettingsFromArray([
            'shopId' => self::SHOP_ID,
            'active' => self::ACTIVE,
            'restyle' => true,
        ]);
    }

    /**
     * @return void
     */
    private function createExpressTestSettings()
    {
        $this->insertExpressCheckoutSettingsFromArray([
            'shopId' => self::SHOP_ID,
            'detailActive' => true,
            'cartActive' => true,
            'loginActive' => true,
            'listingActive' => false,
            'offCanvasActive' => true,
        ]);
    }
}

class DependencyMock extends DependencyProvider
{
    public function __construct()
    {
    }

    /**
     * @return Shop|null
     */
    public function getShop()
    {
        return null;
    }
}
