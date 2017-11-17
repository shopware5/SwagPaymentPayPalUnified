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
use SwagPaymentPayPalUnified\Subscriber\Plus;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class PlusSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

    public function test_can_be_created()
    {
        $subscriber = new Plus(Shopware()->Container());
        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents_has_correct_events()
    {
        $events = Plus::getSubscribedEvents();
        $this->assertEquals('onPostDispatchCheckout', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']);
    }

    public function test_onPostDispatchCheckout_should_return_because_no_settings_exists()
    {
        $subscriber = new Plus(Shopware()->Container());

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
        $subscriber = new Plus(Shopware()->Container());

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
        $subscriber = new Plus(Shopware()->Container());

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
        $subscriber = new Plus(Shopware()->Container());

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

    public function test_onPostDispatchSecure_assigns_nothing_to_view()
    {
        $subscriber = new Plus(Shopware()->Container());
        $this->createTestSettings(false, true, true);

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('shippingPayment');
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedRestylePaymentSelection'));
    }

    public function test_onPostDispatchSecure_sets_restyle_correctly_if_plus_is_inactive()
    {
        $subscriber = new Plus(Shopware()->Container());
        $this->createTestSettings(true, false, true);

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('shippingPayment');
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertFalse((bool) $view->getAssign('paypalUnifiedRestylePaymentSelection'));
    }

    public function test_onPostDispatchSecure_sets_restyle_correctly_if_plus_both_is_inactive()
    {
        $subscriber = new Plus(Shopware()->Container());
        $this->createTestSettings(true, false, false);

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('shippingPayment');
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertFalse((bool) $view->getAssign('paypalUnifiedRestylePaymentSelection'));
    }

    /**
     * @param bool $active
     * @param bool $plusActive
     * @param bool $restylePaymentSelection
     */
    private function createTestSettings($active = true, $plusActive = true, $restylePaymentSelection = false)
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'clientId' => 'test',
            'clientSecret' => 'test',
            'sandbox' => true,
            'showSidebarLogo' => true,
            'logoImage' => 'TEST',
            'active' => $active,
        ]);

        $this->insertPlusSettingsFromArray([
            'shopId' => 1,
            'active' => $plusActive,
            'restyle' => $restylePaymentSelection,
        ]);
    }
}
