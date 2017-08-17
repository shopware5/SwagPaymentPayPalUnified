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

use SwagPaymentPayPalUnified\Subscriber\InContext;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class InContextSubscriberTest extends \PHPUnit_Framework_TestCase
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
        $events = InContext::getSubscribedEvents();

        $this->assertCount(2, $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']);

        $this->assertEquals('addInContextButton', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'][0][0]);
        $this->assertEquals('addInContextInfoToRequest', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'][1][0]);
    }

    public function test_addInContextButton_return_wrong_action()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('foo');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, null),
        ]);

        $subscriber = $this->getSubscriber();

        $this->assertNull($subscriber->addInContextButton($enlightEventArgs));
    }

    public function test_addInContextButton_return_unified_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, null),
        ]);

        $this->importSettings();

        $subscriber = $this->getSubscriber();

        $this->assertNull($subscriber->addInContextButton($enlightEventArgs));
    }

    public function test_addInContextButton_return_not_use_in_context()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, null),
        ]);

        $this->importSettings(true);

        $subscriber = $this->getSubscriber();

        $this->assertNull($subscriber->addInContextButton($enlightEventArgs));
    }

    public function test_addInContextButton_right_template_assigns()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, null),
        ]);

        $this->importSettings(true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addInContextButton($enlightEventArgs);

        $this->assertTrue($view->getAssign('paypalUnifiedModeSandbox'));
        $this->assertTrue($view->getAssign('paypalUnifiedUseInContext'));
    }

    public function test_addInContextInfoToRequest_returns_because_wrong_action()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('fake');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();

        $this->assertNull($subscriber->addInContextInfoToRequest($enlightEventArgs));
    }

    public function test_addInContextInfoToRequest_returns_because_wrong_param()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();

        $this->assertNull($subscriber->addInContextInfoToRequest($enlightEventArgs));
    }

    public function test_addInContextInfoToRequest_returns_because_no_redirect()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');
        $request->setParam('useInContext', true);

        $response = new \Enlight_Controller_Response_ResponseTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
            'response' => $response,
        ]);

        $subscriber = $this->getSubscriber();

        $this->assertNull($subscriber->addInContextInfoToRequest($enlightEventArgs));
    }

    public function test_addInContextInfoToRequest_returns_because_redirect()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');
        $request->setParam('useInContext', true);

        $response = new \Enlight_Controller_Response_ResponseTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
            'request' => $request,
            'response' => $response,
        ]);

        $subscriber = $this->getSubscriber();

        $reflectionClass = new \ReflectionClass(\Enlight_Controller_Response_ResponseTestCase::class);
        $prop = $reflectionClass->getProperty('_isRedirect');
        $prop->setAccessible(true);
        $prop->setValue($response, true);

        $subscriber->addInContextInfoToRequest($enlightEventArgs);

        $this->assertEquals('http://localhost/PaypalUnified/gateway/useInContext/1', $response->getHeader('Location'));
        $this->assertEquals(302, $response->getHttpResponseCode());
    }

    /**
     * @param bool $active
     * @param bool $useInContext
     * @param bool $sandboxMode
     */
    private function importSettings($active = false, $useInContext = false, $sandboxMode = false)
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'active' => $active,
            'sandbox' => $sandboxMode,
            'useInContext' => $useInContext,
            'logoImage' => 'None',
            'clientId' => 'test',
            'clientSecret' => 'test',
        ]);
    }

    /**
     * @return InContext
     */
    private function getSubscriber()
    {
        return new InContext(
            Shopware()->Container()->get('models'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.settings_service')
        );
    }
}
