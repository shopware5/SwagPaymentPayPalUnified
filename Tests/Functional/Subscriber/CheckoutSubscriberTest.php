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
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class CheckoutSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

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

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedUsePlus'));
    }

    public function test_onPostDispatchCheckout_should_return_because_the_action_is_invalid()
    {
        $subscriber = new Checkout(
            Shopware()->Container(),
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('paypal_unified.dependency_provider')
        );

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('invalidSuperAction');

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $response = new \Enlight_Controller_Response_ResponseTestCase();

        $this->createTestSettings();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedUsePlus'));
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

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');

        $response = new \Enlight_Controller_Response_ResponseTestCase();

        $this->createTestSettings();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertTrue((bool) $view->getAssign('paypalUnifiedUsePlus'));
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
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');
        $request->setParam('paypal_unified_error_code', 5);

        $response = new \Enlight_Controller_Response_ResponseTestCase();

        $this->createTestSettings();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertTrue((bool) $view->getAssign('paypalUnifiedUsePlus'));
        $this->assertEquals('5', $view->getAssign('paypalUnifiedErrorCode'));
    }

    private function createTestSettings()
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'clientId' => 'test',
            'clientSecret' => 'test',
            'sandbox' => true,
            'showSidebarLogo' => true,
            'logoImage' => 'TEST',
            'active' => true,
        ]);

        $this->insertPlusSettingsFromArray([
            'shopId' => 1,
            'active' => true,
        ]);
    }
}
