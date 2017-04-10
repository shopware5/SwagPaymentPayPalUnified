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

use Enlight_Template_Manager;
use SwagPaymentPayPalUnified\Subscriber\Checkout;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class CheckoutSubscriberTest extends \Enlight_Components_Test_Controller_TestCase
{
    use DatabaseTestCaseTrait;

    public function test_can_be_created()
    {
        $subscriber = new Checkout(Shopware()->Container(), Shopware()->Container()->get('paypal_unified.settings_service'), Shopware()->Container()->get('paypal_unified.dependency_provider'));
        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents_has_correct_events()
    {
        $events = Checkout::getSubscribedEvents();
        $this->assertEquals('onPostDispatchCheckout', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']);
    }

    public function test_onPostDispatchCheckout_should_return_because_no_settings_exists()
    {
        $subscriber = new Checkout(
            Shopware()->Container(),
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('paypal_unified.dependency_provider')
        );

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $this->Request()->setActionName('finish');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($this->Request(), $view, $this->Response()),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertNull($view->getAssign('usePayPalPlus'));
    }

    public function test_onPostDispatchCheckout_should_return_because_the_action_is_invalid()
    {
        $subscriber = new Checkout(
            Shopware()->Container(),
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('paypal_unified.dependency_provider')
        );

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $this->Request()->setActionName('invalidSuperAction');

        $this->createTestSettings();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($this->Request(), $view, $this->Response()),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertNull($view->getAssign('usePayPalPlus'));
    }

    public function test_onPostDispatchCheckout_should_assign_value_usePayPalPlus()
    {
        $subscriber = new Checkout(
            Shopware()->Container(),
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('paypal_unified.dependency_provider')
        );

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $this->Request()->setActionName('finish');

        $this->createTestSettings();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($this->Request(), $view, $this->Response()),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertTrue((bool) $view->getAssign('usePayPalPlus'));
    }

    public function test_onPostDispatchCheckout_should_assign_error_code()
    {
        $subscriber = new Checkout(
            Shopware()->Container(),
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('paypal_unified.dependency_provider')
        );

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $this->Request()->setActionName('finish');
        $this->Request()->setParam('paypal_unified_error_code', 5);

        $this->createTestSettings();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($this->Request(), $view, $this->Response()),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertTrue((bool) $view->getAssign('usePayPalPlus'));
        $this->assertEquals('5', $view->getAssign('paypal_unified_error_code'));
    }

    private function createTestSettings()
    {
        $settingsParams = [
            ':shopId' => 1,
            ':clientId' => 'TEST',
            ':clientSecret' => 'TEST',
            ':sandbox' => 1,
            ':showSidebarLogo' => 1,
            ':logoImage' => 'TEST',
            ':plusActive' => 1,
            ':plusRestyle' => 0,
            ':active' => 1,
        ];

        $sql = 'INSERT INTO swag_payment_paypal_unified_settings
                (shop_id, active, client_id, client_secret, sandbox, show_sidebar_logo, logo_image, plus_active, plus_restyle)
                VALUES (:shopId, :active, :clientId, :clientSecret, :sandbox, :showSidebarLogo, :logoImage, :plusActive, :plusRestyle)';

        Shopware()->Db()->executeUpdate($sql, $settingsParams);
    }
}
