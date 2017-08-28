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

use Enlight\Event\SubscriberInterface;

class BackendOrder implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware\Models\Order\Repository::getBackendOrdersQueryBuilder::after' => 'addAttributesToOrders',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order' => 'onPostDispatchOrder',
        ];
    }

    /**
     * additionally select the order attributes, so the PayPal attribute could be used in the next event
     *
     * @param \Enlight_Hook_HookArgs $args
     */
    public function addAttributesToOrders(\Enlight_Hook_HookArgs $args)
    {
        /** @var \Shopware\Components\Model\QueryBuilder $queryBuilder */
        $queryBuilder = $args->getReturn();

        $joinParts = $queryBuilder->getDQLPart('join')['orders'];
        $joinAttributePartMissing = true;
        $joinAttributeAlias = 'attribute';

        /** @var \Doctrine\ORM\Query\Expr\Join $joinPart */
        foreach ($joinParts as $joinPart) {
            if ($joinPart->getJoin() === 'orders.attribute') {
                $joinAttributePartMissing = false;
                $joinAttributeAlias = $joinPart->getAlias();
            }
        }

        if ($joinAttributePartMissing) {
            $queryBuilder->leftJoin('orders.attribute', $joinAttributeAlias);
        }

        $selectParts = $queryBuilder->getDQLPart('select');
        $selectAttributePartIsMissing = true;

        /** @var \Doctrine\ORM\Query\Expr\Select $selectPart */
        foreach ($selectParts as $selectPart) {
            foreach ($selectPart->getParts() as $part) {
                if ($part === $joinAttributeAlias) {
                    $selectAttributePartIsMissing = false;
                }
            }
        }

        if ($selectAttributePartIsMissing) {
            $queryBuilder->addSelect($joinAttributeAlias);
        }

        $args->setReturn($queryBuilder);
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

        foreach ($orders as &$order) {
            if (!$order['attribute']['swagPaypalUnifiedPaymentType']) {
                continue;
            }

            $order['payment']['description'] = $order['attribute']['swagPaypalUnifiedPaymentType'];
        }
        unset($order);

        $view->assign('data', $orders);
    }
}
