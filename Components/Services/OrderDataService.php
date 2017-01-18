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

namespace SwagPaymentPayPalUnified\Components\Services;

use Doctrine\ORM\EntityManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use Shopware\Components\Model\ModelManager;

class OrderDataService
{
    /** @var EntityManager $em */
    private $modelManager;

    /**
     * OrderDataService constructor.
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $orderNumber
     * @param int $paymentStatusId
     * @return bool
     */
    public function applyPaymentStatus($orderNumber, $paymentStatusId)
    {
        /** @var Order $orderModel */
        $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['number' => $orderNumber]);
        /** @var Status $orderStatusModel */
        $orderStatusModel = $this->modelManager->getRepository(Status::class)->find($paymentStatusId);

        $orderModel->setPaymentStatus($orderStatusModel);

        $this->modelManager->persist($orderModel);
        $this->modelManager->flush($orderModel);
    }

    /**
     * @param int $orderNumber
     * @param string $transactionId
     */
    public function applyTransactionId($orderNumber, $transactionId)
    {
        /** @var Order $orderModel */
        $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['number' => $orderNumber]);

        $orderModel->setTransactionId($transactionId);

        $this->modelManager->persist($orderModel);
        $this->modelManager->flush($orderModel);
    }
}
