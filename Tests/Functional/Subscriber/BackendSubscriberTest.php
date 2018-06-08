<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Template_Manager;
use SwagPaymentPayPalUnified\Subscriber\Backend;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class BackendSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function test_can_be_created()
    {
        $subscriber = new Backend(__DIR__);
        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents_has_correct_events()
    {
        $events = Backend::getSubscribedEvents();
        $this->assertEquals('onLoadBackendIndex', $events['Enlight_Controller_Action_PostDispatchSecure_Backend_Index']);
        $this->assertEquals('onPostDispatchConfig', $events['Enlight_Controller_Action_PostDispatchSecure_Backend_Config']);
        $this->assertCount(2, $events);
    }

    public function test_onLoadBackendIndex_extends_template()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onLoadBackendIndex($enlightEventArgs);

        $this->assertCount(1, $view->getTemplateDir());
    }

    public function test_onPostDispatchConfig_extends_template()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('load');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber->onPostDispatchConfig($enlightEventArgs);

        $this->assertCount(1, $view->getTemplateDir());
    }
}
