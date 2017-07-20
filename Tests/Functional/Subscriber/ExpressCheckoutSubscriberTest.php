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

use SwagPaymentPayPalUnified\Subscriber\ExpressCheckout as ExpressCheckoutSubscriber;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\ClientService;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class ExpressCheckoutSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

    public function test_construct()
    {
        $subscriber = $this->getSubscriber();

        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $events = ExpressCheckoutSubscriber::getSubscribedEvents();

        $this->assertCount(4, $events);

        $this->assertEquals('loadExpressCheckoutJS', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend']);
        $this->assertEquals('loadExpressCheckoutJS', $events['Enlight_Controller_Action_PostDispatchSecure_Widgets']);
        $this->assertEquals('addExpressCheckoutButtonDetail', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail']);
        $this->assertCount(3, $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']);
    }

    public function test_loadExpressCheckoutJs_assign_paypalUnifiedEcActive_value()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, null),
        ]);

        $this->importSettings(true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->loadExpressCheckoutJS($enlightEventArgs);

        $this->assertTrue($view->getAssign('paypalUnifiedEcActive'));
    }

    public function test_loadExpressCheckoutJs_return_unified_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, null),
        ]);

        $this->importSettings(false, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->loadExpressCheckoutJS($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedEcActive'));
    }

    public function test_loadExpressCheckoutJs_return_ec_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, null),
        ]);

        $this->importSettings(true, false, true);

        $subscriber = $this->getSubscriber();
        $subscriber->loadExpressCheckoutJS($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedEcActive'));
    }

    public function test_addExpressCheckoutButtonCart_return_unified_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, null),
        ]);

        $this->importSettings(false, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedModeSandbox'));
    }

    public function test_addExpressCheckoutButtonCart_return_ec_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, null),
        ]);

        $this->importSettings(true, false, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedModeSandbox'));
    }

    public function test_addExpressCheckoutButtonCart_return_wrongAction()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('fake');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedModeSandbox'));
    }

    public function test_addExpressCheckoutButtonCart_assigns_value_to_cart()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('cart');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        $this->assertTrue($view->getAssign('paypalUnifiedModeSandbox'));
    }

    public function test_addExpressCheckoutButtonCart_assigns_value_to_ajax_cart()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('ajaxCart');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        $this->assertTrue($view->getAssign('paypalUnifiedModeSandbox'));
    }

    public function test_addEcInfoOnConfirm_returns_because_wrong_action()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('fake');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();
        $subscriber->addEcInfoOnConfirm($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedExpressPaymentId'));
    }

    public function test_addEcInfoOnConfirm_returns_because_no_ec()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();
        $subscriber->addEcInfoOnConfirm($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedExpressPaymentId'));
    }

    public function test_addEcInfoOnConfirm_assigns_correct_values_on_confirm_action()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');
        $request->setParam('paymentId', 'TEST_PAYMENT_ID');
        $request->setParam('payerId', 'TEST_PAYER_ID');
        $request->setParam('expressCheckout', true);
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addEcInfoOnConfirm($enlightEventArgs);

        $this->assertEquals('TEST_PAYMENT_ID', $view->getAssign('paypalUnifiedExpressPaymentId'));
        $this->assertEquals('TEST_PAYER_ID', $view->getAssign('paypalUnifiedExpressPayerId'));
        $this->assertTrue($view->getAssign('paypalUnifiedExpressCheckout'));
    }

    public function test_addPaymentInfoToRequest_returns_because_wrong_action()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('fake');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();

        $this->assertNull($subscriber->addPaymentInfoToRequest($enlightEventArgs));
    }

    public function test_addPaymentInfoToRequest_returns_because_wrong_param()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();

        $this->assertNull($subscriber->addPaymentInfoToRequest($enlightEventArgs));
    }

    public function test_addPaymentInfoToRequest_returns_because_no_redirect()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');
        $request->setParam('expressCheckout', true);

        $response = new \Enlight_Controller_Response_ResponseTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
            'response' => $response,
        ]);

        $subscriber = $this->getSubscriber();

        $this->assertNull($subscriber->addPaymentInfoToRequest($enlightEventArgs));
    }

    public function test_addExpressCheckoutButtonDetail_returns_because_unified_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $this->importSettings(false);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function test_addExpressCheckoutButtonDetail_returns_because_ec_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $this->importSettings(true, false);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function test_addExpressCheckoutButtonDetail_returns_because_ec_detail_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $this->importSettings(true, true, false);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function test_addExpressCheckoutButtonDetail_assigns_correct_values()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $this->importSettings(true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        $this->assertTrue($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    /**
     * @param bool $active
     * @param bool $ecActive
     * @param bool $ecDetailActive
     * @param bool $sandboxMode
     */
    private function importSettings($active = false, $ecActive = false, $ecDetailActive = false, $sandboxMode = false)
    {
        $this->insertGeneralSettingsFromArray([
            'active' => $active,
            'shopId' => 1,
            'sandbox' => $sandboxMode,
        ]);

        $this->insertExpressCheckoutSettingsFromArray([
            'active' => $ecActive,
            'detailActive' => $ecDetailActive,
        ]);
    }

    private function getSubscriber()
    {
        Shopware()->Container()->set('paypal_unified.client_service', new ClientService());

        return new ExpressCheckoutSubscriber(
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('session'),
            Shopware()->Container()->get('paypal_unified.payment_resource'),
            Shopware()->Container()->get('paypal_unified.shipping_address_request_service'),
            Shopware()->Container()->get('paypal_unified.payment_builder_service'),
            Shopware()->Container()->get('paypal_unified.logger_service')
        );
    }
}
