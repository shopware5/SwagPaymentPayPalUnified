<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\Models\Settings;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class SettingsServiceTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;

    const SHOP_ID = 1;
    const CLIENT_ID = 'TEST_CLIENT_ID';
    const CLIENT_SECRET = 'TEST_CLIENT_SECRET';
    const SANDBOX = true;
    const SHOW_SIDEBAR_LOGO = false;
    const LOGO_IMAGE = '/test/image/test.png';
    const PLUS_ACTIVE = true;
    const ACTIVE = true;

    public function test_service_available()
    {
        $this->assertNotNull(Shopware()->Container()->get('paypal_unified.settings_service'));
    }

    public function test_getSettings_byShopId()
    {
        $this->createTestSettings();

        /** @var Settings $settingsModel */
        $settingsModel = Shopware()->Container()->get('paypal_unified.settings_service')->getSettings(self::SHOP_ID);

        $this->assertEquals(self::ACTIVE, $settingsModel->getActive());
        $this->assertEquals(self::CLIENT_ID, $settingsModel->getClientId());
        $this->assertEquals(self::CLIENT_SECRET, $settingsModel->getClientSecret());
        $this->assertEquals(self::SANDBOX, $settingsModel->getSandbox());
        $this->assertEquals(self::SHOW_SIDEBAR_LOGO, $settingsModel->getShowSidebarLogo());
        $this->assertEquals(self::LOGO_IMAGE, $settingsModel->getLogoImage());
        $this->assertEquals(self::PLUS_ACTIVE, $settingsModel->getPlusActive());
    }

    public function test_get()
    {
        $this->createTestSettings();

        /** @var SettingsService $settingsService */
        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');
        $this->assertEquals(self::CLIENT_ID, $settingsService->get('client_id'));
        $this->assertEquals(self::CLIENT_SECRET, $settingsService->get('client_secret'));
    }

    public function test_get_without_shop_throws_exception()
    {
        $settingsService = new SettingsService(Shopware()->Container()->get('models'), new DependencyMock());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not retrieve a single setting without a shop instance.');
        $settingsService->get('paypal_active');
    }

    public function test_hasSettings_false()
    {
        /** @var SettingsService $settingsService */
        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');

        $this->assertFalse($settingsService->hasSettings());
    }

    public function test_hasSettings_false_because_no_shop_is_available()
    {
        $settingsService = new SettingsService(Shopware()->Container()->get('models'), new DependencyMock());

        $this->assertFalse($settingsService->hasSettings());
    }

    public function test_hasSettings_true()
    {
        $this->createTestSettings();

        /** @var SettingsService $settingsService */
        $settingsService = Shopware()->Container()->get('paypal_unified.settings_service');

        $this->assertTrue($settingsService->hasSettings());
    }

    private function createTestSettings()
    {
        $settingsParams = [
            ':shopId' => self::SHOP_ID,
            ':clientId' => self::CLIENT_ID,
            ':clientSecret' => self::CLIENT_SECRET,
            ':sandbox' => self::SANDBOX,
            ':showSidebarLogo' => self::SHOW_SIDEBAR_LOGO,
            ':logoImage' => self::LOGO_IMAGE,
            ':plusActive' => self::PLUS_ACTIVE,
            ':active' => self::ACTIVE,
        ];

        $sql = 'INSERT INTO swag_payment_paypal_unified_settings
                (shop_id, active, client_id, client_secret, sandbox, show_sidebar_logo, logo_image, plus_active)
                VALUES (:shopId, :active, :clientId, :clientSecret, :sandbox, :showSidebarLogo, :logoImage, :plusActive)';

        Shopware()->Db()->executeUpdate($sql, $settingsParams);
    }
}

class DependencyMock extends DependencyProvider
{
    public function __construct()
    {
    }

    public function getShop()
    {
        return null;
    }
}
