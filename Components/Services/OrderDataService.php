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

use Doctrine\DBAL\Connection;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\SDK\PaymentType;
use SwagPaymentPayPalUnified\SDK\Structs\Payment;

class OrderDataService
{
    /** @var ModelManager $em */
    private $modelManager;

    /** @var Connection $dbalConnection */
    private $dbalConnection;

    /** @var \Shopware_Components_Config $config */
    private $config;

    /**
     * OrderDataService constructor.
     *
     * @param ModelManager $modelManager
     * @param Connection $dbalConnection
     * @param \Shopware_Components_Config $config
     */
    public function __construct(
        ModelManager $modelManager,
        Connection $dbalConnection,
        \Shopware_Components_Config $config
    ) {
        $this->modelManager = $modelManager;
        $this->dbalConnection = $dbalConnection;
        $this->config = $config;
    }

    /**
     * @param string $orderNumber
     * @param int $paymentStatusId
     */
    public function applyPaymentStatus($orderNumber, $paymentStatusId)
    {
        /** @var Order $orderModel */
        $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['number' => $orderNumber]);

        if (!$orderModel) {
            return false;
        }

        /** @var Status $orderStatusModel */
        $orderStatusModel = $this->modelManager->getRepository(Status::class)->find($paymentStatusId);

        $orderModel->setPaymentStatus($orderStatusModel);

        $this->modelManager->persist($orderModel);
        $this->modelManager->flush($orderModel);

        return true;
    }

    /**
     * @param int $orderNumber
     * @param string $transactionId
     * @return bool
     */
    public function applyTransactionId($orderNumber, $transactionId)
    {
        /** @var Order $orderModel */
        $orderModel = $this->modelManager->getRepository(Order::class)->findOneBy(['number' => $orderNumber]);

        if (!$orderModel) {
            return false;
        }

        $orderModel->setTransactionId($transactionId);

        $this->modelManager->persist($orderModel);
        $this->modelManager->flush($orderModel);

        return true;
    }

    /**
     * @param int $orderNumber
     * @param Payment $payment
     * @see PaymentType
     */
    public function applyPaymentTypeAttribute($orderNumber, $payment)
    {
        if ($payment->getPaymentInstruction() !== null) {
            $paymentType = PaymentType::PAYPAL_INVOICE;
        } elseif ($this->config->get('usePayPalPlus') === true) {
            $paymentType = PaymentType::PAYPAL_PLUS;
        } else {
            $paymentType = PaymentType::PAYPAL_CLASSIC;
        }

        $builder = $this->dbalConnection->createQueryBuilder();

        //Since joins are being stripped out, we have to select the correct orderId by a sub query.
        $subQuery =  $this->dbalConnection->createQueryBuilder()
            ->select('o.id')
            ->from('s_order', 'o')
            ->where('o.ordernumber = :orderNumber')
            ->getSQL();

        $builder->update('s_order_attributes', 'oa')
            ->set('oa.paypal_payment_type', ':paymentType')
            ->where('oa.orderID = (' . $subQuery . ')')
            ->setParameters([
                ':orderNumber' => $orderNumber,
                ':paymentType' => $paymentType
            ])->execute();
    }
}
