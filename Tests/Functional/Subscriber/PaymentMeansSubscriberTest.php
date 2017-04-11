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

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\Subscriber\PaymentMeans;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\PayPalUnifiedPaymentIdTrait;

class PaymentMeansSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;
    use PayPalUnifiedPaymentIdTrait;

    public function test_can_be_created()
    {
        $subscriber = new PaymentMeans(Shopware()->Container()->get('dbal_connection'), new SettingsServiceMock());
        $this->assertEquals(PaymentMeans::class, get_class($subscriber));
    }

    public function test_getSubscribedEvents()
    {
        $events = PaymentMeans::getSubscribedEvents();
        $this->assertCount(1, $events);
        $this->assertEquals('onFilterPaymentMeans', $events['Shopware_Modules_Admin_GetPaymentMeans_DataFilter']);
    }

    public function test_onFilterPaymentMeans_without_available_methods()
    {
        $subscriber = new PaymentMeans(Shopware()->Container()->get('dbal_connection'), new SettingsServiceMock());

        $args = new EventArgsMockWithoutReturn();
        $subscriber->onFilterPaymentMeans($args);

        $this->assertCount(0, $args->result);
    }

    public function test_onFilterPaymentMeans_without_unified_method()
    {
        $subscriber = new PaymentMeans(Shopware()->Container()->get('dbal_connection'), new SettingsServiceMock());

        $args = new EventArgsMockWithoutUnifiedReturn();
        $subscriber->onFilterPaymentMeans($args);

        $this->assertCount(5, $args->result);
    }

    public function test_onFilterPaymentMeans_has_unified_method()
    {
        $subscriber = new PaymentMeans(Shopware()->Container()->get('dbal_connection'), Shopware()->Container()->get('paypal_unified.settings_service'));
        $this->createTestSettings();

        $args = new EventArgsMockWithUnifiedReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        $this->assertCount(5, $result);
        $this->assertEquals($this->getUnifiedPaymentId(), $result[4]['id']);
    }

    public function test_onFilterPaymentMeans_has_no_unified_method_because_the_settings_dont_exist()
    {
        $subscriber = new PaymentMeans(Shopware()->Container()->get('dbal_connection'), new SettingsServiceMock());
        $this->createTestSettings();

        $args = new EventArgsMockWithUnifiedReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        $this->assertCount(4, $result);
    }

    private function createTestSettings()
    {
        $settingsParams = [
            ':shopId' => 1,
            ':clientId' => 'TEST',
            ':clientSecret' => 'TEST',
            ':sandbox' => true,
            ':showSidebarLogo' => true,
            ':logoImage' => 'None',
            ':plusActive' => true,
            ':active' => true,
        ];

        $sql = 'INSERT INTO swag_payment_paypal_unified_settings
                (shop_id, active, client_id, client_secret, sandbox, show_sidebar_logo, logo_image, plus_active)
                VALUES (:shopId, :active, :clientId, :clientSecret, :sandbox, :showSidebarLogo, :logoImage, :plusActive)';

        Shopware()->Db()->executeUpdate($sql, $settingsParams);
    }
}

class SettingsServiceMock implements SettingsServiceInterface
{
    public function get($column)
    {
    }

    public function hasSettings()
    {
    }

    public function getSettings($shopId = null)
    {
    }
}
class EventArgsMockWithoutReturn extends \Enlight_Event_EventArgs
{
    public $result;

    public function getReturn()
    {
        return [];
    }

    public function setReturn($result)
    {
        $this->result = $result;
    }
}
class EventArgsMockWithoutUnifiedReturn extends \Enlight_Event_EventArgs
{
    public $result;

    public function getReturn()
    {
        return [
            ['id' => 0],
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
        ];
    }

    public function setReturn($result)
    {
        $this->result = $result;
    }
}
class EventArgsMockWithUnifiedReturn extends \Enlight_Event_EventArgs
{
    use PayPalUnifiedPaymentIdTrait;

    public $result;

    public function getReturn()
    {
        return [
            ['id' => 0],
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => $this->getUnifiedPaymentId()],
        ];
    }

    public function setReturn($result)
    {
        $this->result = $result;
    }
}
