<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Subscriber\BackendOrder;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class BackendOrderSubscriberTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function testCanBeCreated()
    {
        $subscriber = $this->getBackendOrderSubscriber();
        static::assertNotNull($subscriber);
    }

    public function testGetSubscribedEventsHasCorrectEvents()
    {
        $events = BackendOrder::getSubscribedEvents();
        static::assertCount(1, $events);
        static::assertSame('onPostDispatchOrder', $events['Enlight_Controller_Action_PostDispatchSecure_Backend_Order']);
    }

    public function testOnPostDispatchOrderWrongAction()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('foo');
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $result = $this->getBackendOrderSubscriber()->onPostDispatchOrder($enlightEventArgs);

        static::assertNull($result);
    }

    public function testOnPostDispatchOrderGetList()
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

        static::assertSame($result[0]['payment']['description'], 'PayPalPlus');
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
            'id' => 99991,
        ]);

        $connection->insert('s_order_attributes', [
            'orderID' => 9998,
            'swag_paypal_unified_payment_type' => 'PayPalPlus',
        ]);

        $connection->insert('s_order_attributes', [
            'orderID' => 99991,
            'swag_paypal_unified_payment_type' => null,
        ]);
    }
}
