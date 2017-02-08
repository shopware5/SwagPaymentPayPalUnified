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

use Shopware\Components\Logger;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookEventTypes;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookHandler;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;

class SaleComplete implements WebhookHandler
{
    /**
     * @var Logger
     */
    private $pluginLogger;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param Logger       $pluginLogger
     * @param ModelManager $modelManager
     */
    public function __construct(Logger $pluginLogger, ModelManager $modelManager)
    {
        $this->pluginLogger = $pluginLogger;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventType()
    {
        return WebhookEventTypes::PAYMENT_SALE_COMPLETED;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(Webhook $webhook)
    {
        try {
            /** @var Order $orderRepository */
            $orderRepository = $this->modelManager->getRepository(Order::class)->findOneBy(['transactionId' => $webhook->getSummary()['parent_payment']]);
            /** @var Status $orderStatusModel */
            $orderStatusModel = $this->modelManager->getRepository(Status::class)->find(PaymentStatus::PAYMENT_STATUS_APPROVED);

            if ($orderRepository === null) {
                $this->pluginLogger->error('PayPal Unified: Could not find associated order with the transactionId ' . $webhook->getSummary()['parent_payment']);

                return;
            }

            //Set the payment status to "completely payed"
            $orderRepository->setPaymentStatus($orderStatusModel);

            $this->modelManager->flush($orderRepository);
        } catch (\Exception $ex) {
            $this->pluginLogger->error('PayPal Unified: (Webhook: SaleComplete) Could not write entity to database', [$ex->getMessage()]);
        }
    }
}
