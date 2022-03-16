<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use PHPUnit\Framework\TestCase;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\Models\Settings\Installments as InstallmentsSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\Plus as PlusSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class SettingsServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

    const SHOP_ID = 1;
    const CLIENT_ID = 'TEST_CLIENT_ID';
    const CLIENT_SECRET = 'TEST_CLIENT_SECRET';
    const SANDBOX = true;
    const SHOW_SIDEBAR_LOGO = false;
    const PLUS_ACTIVE = true;
    const ACTIVE = true;

    public function testServiceAvailable()
    {
        static::assertNotNull(Shopware()->Container()->get('paypal_unified.settings_service'));
    }

    public function testGetGeneralSettingsByShopId()
    {
        $this->createTestSettings();

        $settingsModel = Shopware()->Container()->get('paypal_unified.settings_service')->getSettings(self::SHOP_ID);
        static::assertInstanceOf(General::class, $settingsModel);

        static::assertTrue($settingsModel->getActive());
        static::assertSame(self::CLIENT_ID, $settingsModel->getClientId());
        static::assertSame(self::CLIENT_SECRET, $settingsModel->getClientSecret());
        static::assertTrue($settingsModel->getSandbox());
        static::assertFalse($settingsModel->getShowSidebarLogo());
    }

    public function testGet()
    {
        $this->createTestSettings();

        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');
        static::assertSame(self::CLIENT_ID, $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_CLIENT_ID));
        static::assertSame(self::CLIENT_SECRET, $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_CLIENT_SECRET));
    }

    public function testGetWithoutShopThrowsException()
    {
        $this->createTestSettings();
        $settingsService = new SettingsService(Shopware()->Container()->get('models'), new DependencyMock());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not retrieve a single setting without a shop instance.');
        $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_INTENT);
    }

    public function testHasSettingsFalse()
    {
        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');

        static::assertFalse($settingsService->hasSettings());
    }

    public function testHasSettingsFalseBecauseNoShopIsAvailable()
    {
        $settingsService = new SettingsService(Shopware()->Container()->get('models'), new DependencyMock());

        static::assertFalse($settingsService->hasSettings());
    }

    public function testHasSettingsTrue()
    {
        $this->createTestSettings();

        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');

        static::assertTrue($settingsService->hasSettings());
    }

    public function testGetSettingsInstallments()
    {
        $this->createInstallmentsTestSettings();

        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');

        $installmentsSettings = $settingsService->getSettings(self::SHOP_ID, SettingsTable::INSTALLMENTS);
        static::assertInstanceOf(InstallmentsSettingsModel::class, $installmentsSettings);

        static::assertTrue($installmentsSettings->getAdvertiseInstallments());
    }

    public function testGetSettingsPlus()
    {
        $this->createPlusTestSettings();

        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');

        $plusSettings = $settingsService->getSettings(self::SHOP_ID, SettingsTable::PLUS);
        static::assertInstanceOf(PlusSettingsModel::class, $plusSettings);

        static::assertTrue($plusSettings->getActive());
        static::assertTrue($plusSettings->getRestyle());
    }

    public function testHasSettingsInstallments()
    {
        $this->createInstallmentsTestSettings();

        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');

        static::assertTrue($settingsService->hasSettings(SettingsTable::INSTALLMENTS));
    }

    public function testHasSettingsPlus()
    {
        $this->createPlusTestSettings();

        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');

        static::assertTrue($settingsService->hasSettings(SettingsTable::PLUS));
    }

    public function testHasSettingsExpress()
    {
        $this->createExpressTestSettings();

        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');

        static::assertTrue($settingsService->hasSettings(SettingsTable::EXPRESS_CHECKOUT));
    }

    public function testGetExpress()
    {
        $this->createExpressTestSettings();

        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');

        static::assertTrue((bool) $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_CART_ACTIVE, SettingsTable::EXPRESS_CHECKOUT));
        static::assertTrue((bool) $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_DETAIL_ACTIVE, SettingsTable::EXPRESS_CHECKOUT));
        static::assertTrue((bool) $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_LOGIN_ACTIVE, SettingsTable::EXPRESS_CHECKOUT));
        static::assertTrue((bool) $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_OFF_CANVAS_ACTIVE, SettingsTable::EXPRESS_CHECKOUT));
    }

    public function testGetSettingsReturnsNullWithoutCorrectTable()
    {
        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');

        static::assertNull($settingsService->getSettings(self::SHOP_ID, 'THIS_TABLE_DOES_NOT_EXIST'));
    }

    public function testGetWillThrowExceptionWithWrongSettingsType()
    {
        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');

        $this->expectException(\RuntimeException::class);
        $settingsService->get(SettingsServiceInterface::SETTING_GENERAL_INTENT, 'THIS_TABLE_DOES_NOT_EXIST');
    }

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

    private function createInstallmentsTestSettings()
    {
        $this->insertInstallmentsSettingsFromArray([
            'shopId' => self::SHOP_ID,
            'advertiseInstallments' => true,
        ]);
    }

    private function createPlusTestSettings()
    {
        $this->insertPlusSettingsFromArray([
            'shopId' => self::SHOP_ID,
            'active' => self::ACTIVE,
            'restyle' => true,
        ]);
    }

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
