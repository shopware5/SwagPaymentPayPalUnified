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

namespace SwagPaymentPayPalUnified\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;

class BackendOrder implements SubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
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
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatchOrder(\Enlight_Controller_ActionEventArgs $args)
    {
        if ($args->getRequest()->getActionName() !== 'getList') {
            return;
        }

        $view = $args->getSubject()->View();
        $orders = $view->getAssign('data');
        $orderIds = array_column($orders, 'id');

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
