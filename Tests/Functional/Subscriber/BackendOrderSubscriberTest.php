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

use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Order\Order;
use SwagPaymentPayPalUnified\Subscriber\BackendOrder;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class BackendOrderSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function test_can_be_created()
    {
        $subscriber = new BackendOrder();
        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents_has_correct_events()
    {
        $events = BackendOrder::getSubscribedEvents();
        $this->assertCount(2, $events);
        $this->assertEquals('addAttributesToOrders', $events['Shopware\Models\Order\Repository::getBackendOrdersQueryBuilder::after']);
        $this->assertEquals('onPostDispatchOrder', $events['Enlight_Controller_Action_PostDispatchSecure_Backend_Order']);
    }

    public function test_addAttributesToOrders_without_attribute_parts()
    {
        /** @var QueryBuilder $initialQueryBuilder */
        $initialQueryBuilder = Shopware()->Container()->get('models')->createQueryBuilder();
        $initialQueryBuilder->addSelect('orders')
            ->from(Order::class, 'orders');
        $hookArgs = new \Enlight_Hook_HookArgs();

        $hookArgs->setReturn($initialQueryBuilder);

        $this->getBackendOrderSubscriber()->addAttributesToOrders($hookArgs);

        $queryBuilder = $hookArgs->getReturn();

        $this->assertCount(2, $queryBuilder->getDQLPart('select'));
        $this->assertCount(1, $queryBuilder->getDQLPart('join')['orders']);

        $this->assertSame($queryBuilder->getDQLPart('select')[1]->getParts()[0], 'attribute');
    }

    public function test_addAttributesToOrders_with_join_attribute_part()
    {
        /** @var QueryBuilder $initialQueryBuilder */
        $initialQueryBuilder = Shopware()->Container()->get('models')->createQueryBuilder();
        $initialQueryBuilder->addSelect('orders')
            ->from(Order::class, 'orders')
            ->leftJoin('orders.attribute', 'attribute');
        $hookArgs = new \Enlight_Hook_HookArgs();

        $hookArgs->setReturn($initialQueryBuilder);

        $this->getBackendOrderSubscriber()->addAttributesToOrders($hookArgs);

        $queryBuilder = $hookArgs->getReturn();

        $this->assertCount(2, $queryBuilder->getDQLPart('select'));
        $this->assertCount(1, $queryBuilder->getDQLPart('join')['orders']);

        $this->assertSame($queryBuilder->getDQLPart('select')[1]->getParts()[0], 'attribute');
    }

    public function test_addAttributesToOrders_with_join_and_select_attribute_parts()
    {
        /** @var QueryBuilder $initialQueryBuilder */
        $initialQueryBuilder = Shopware()->Container()->get('models')->createQueryBuilder();
        $initialQueryBuilder->addSelect('orders')
            ->from(Order::class, 'orders')
            ->leftJoin('orders.attribute', 'attribute')
            ->addSelect('attribute');
        $hookArgs = new \Enlight_Hook_HookArgs();

        $hookArgs->setReturn($initialQueryBuilder);

        $this->getBackendOrderSubscriber()->addAttributesToOrders($hookArgs);

        $queryBuilder = $hookArgs->getReturn();

        $this->assertCount(2, $queryBuilder->getDQLPart('select'));
        $this->assertCount(1, $queryBuilder->getDQLPart('join')['orders']);

        $this->assertSame($queryBuilder->getDQLPart('select')[1]->getParts()[0], 'attribute');
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
        return new BackendOrder();
    }
}
