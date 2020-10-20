<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;

class BackendOrder implements SubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order' => 'onPostDispatchOrder',
        ];
    }

    /**
     * change the payment name to show which PayPal payment was selected by the customer
     */
    public function onPostDispatchOrder(\Enlight_Controller_ActionEventArgs $args)
    {
        if ($args->getRequest()->getActionName() !== 'getList') {
            return;
        }

        $view = $args->getSubject()->View();
        $orders = $view->getAssign('data');
        $orderIds = \array_column($orders, 'id');

        $query = $this->connection->createQueryBuilder();
        $query->select(['orderID', 'swag_paypal_unified_payment_type'])
            ->from('s_order_attributes')
            ->where('orderID IN(:orderIds)')
            ->setParameter('orderIds', $orderIds, Connection::PARAM_INT_ARRAY);

        $payPalPaymentTypes = $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($orders as &$order) {
            if (!isset($payPalPaymentTypes[$order['id']])) {
                continue;
            }

            $order['payment']['description'] = $payPalPaymentTypes[$order['id']];
        }
        unset($order);

        $view->assign('data', $orders);
    }
}
