<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use SwagPaymentPayPalUnified\Subscriber\BackendOrder;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class BackendOrderSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;

    public function test_can_be_created()
    {
        $subscriber = $this->getBackendOrderSubscriber();
        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents_has_correct_events()
    {
        $events = BackendOrder::getSubscribedEvents();
        $this->assertCount(1, $events);
        $this->assertSame('onPostDispatchOrder', $events['Enlight_Controller_Action_PostDispatchSecure_Backend_Order']);
    }

    public function test_onPostDispatchOrder_wrong_action()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('foo');
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $result = $this->getBackendOrderSubscriber()->onPostDispatchOrder($enlightEventArgs);

        $this->assertNull($result);
    }

    public function test_onPostDispatchOrder_getList()
    {
        $this->prepareOrderAttributes();
        $view = new ViewMock(new \Enlight_Template_Manager());
        $view->assign('data', require __DIR__ . '/_fixtures/BackendOrderData.php');

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('getList');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->getBackendOrderSubscriber()->onPostDispatchOrder($enlightEventArgs);

        $result = $view->getAssign('data');

        $this->assertSame($result[0]['payment']['description'], 'PayPalPlus');
    }

    /**
     * @return BackendOrder
     */
    private function getBackendOrderSubscriber()
    {
        return new BackendOrder(Shopware()->Container()->get('dbal_connection'));
    }

    private function prepareOrderAttributes()
    {
        $connection = Shopware()->Container()->get('dbal_connection');

        $connection->insert('s_order', [
            'id' => 9998,
        ]);

        $connection->insert('s_order', [
            'id' => 9999,
        ]);

        $connection->insert('s_order_attributes', [
            'orderID' => 9998,
            'swag_paypal_unified_payment_type' => 'PayPalPlus',
        ]);

        $connection->insert('s_order_attributes', [
            'orderID' => 9999,
            'swag_paypal_unified_payment_type' => null,
        ]);
    }
}
