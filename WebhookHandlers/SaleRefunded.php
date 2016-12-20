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

namespace SwagPaymentPayPalUnified\WebhookHandlers;

use Doctrine\ORM\EntityManager;
use Shopware\Components\Logger;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\SDK\Components\Webhook\WebhookEventTypes;
use SwagPaymentPayPalUnified\SDK\Components\Webhook\WebhookHandler;
use SwagPaymentPayPalUnified\SDK\Structs\Webhook;

class SaleRefunded implements WebhookHandler
{
    /** Status: Refunded */
    const PAYMENT_STATUS_REFUNDED = 20;

    /** @var Logger $pluginLogger*/
    private $pluginLogger;

    /** @var EntityManager */
    private $em;

    /**
     * @param Logger $pluginLogger
     * @param EntityManager $em
     */
    public function __construct(Logger $pluginLogger, EntityManager $em)
    {
        $this->pluginLogger = $pluginLogger;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventType()
    {
        return WebhookEventTypes::PAYMENT_SALE_REFUNDED;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(Webhook $webhook)
    {
        /** @var Order $orderModel */
        $orderModel = $this->em->getRepository(Order::class)->findOneBy(['transactionId' => $webhook->getSummary()['parent_payment']]);
        /** @var Status $orderStatusModel */
        $orderStatusModel = $this->em->getRepository(Status::class)->find(self::PAYMENT_STATUS_REFUNDED);

        if ($orderModel === null) {
            $this->pluginLogger->error('PayPal Unified: Could not find associated order with the transactionId ' . $webhook->getSummary()['parent_payment']);
            return;
        }

        //Set the payment status to "Refunded"
        $orderModel->setPaymentStatus($orderStatusModel);

        $this->em->flush($orderModel);
    }
}
