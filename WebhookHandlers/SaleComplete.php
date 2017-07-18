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

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookEventTypes;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookHandler;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;

class SaleComplete implements WebhookHandler
{
    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param LoggerServiceInterface $logger
     * @param ModelManager           $modelManager
     */
    public function __construct(LoggerServiceInterface $logger, ModelManager $modelManager)
    {
        $this->logger = $logger;
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
            $orderRepository = $this->modelManager->getRepository(Order::class)->findOneBy(['temporaryId' => $webhook->getResource()['parent_payment']]);
            /** @var Status $orderStatusModel */
            $orderStatusModel = $this->modelManager->getRepository(Status::class)->find(PaymentStatus::PAYMENT_STATUS_APPROVED);

            if ($orderRepository === null) {
                $this->logger->error('[SaleComplete-Webhook] Could not find associated order with the temporaryID ' . $webhook->getResource()['parent_payment'], ['webhook' => $webhook->toArray()]);

                return false;
            }

            //Set the payment status to "completely payed"
            $orderRepository->setPaymentStatus($orderStatusModel);

            $this->modelManager->flush($orderRepository);

            return true;
        } catch (\Exception $ex) {
            $this->logger->error('[SaleComplete-Webhook] Could not update entity', ['message' => $ex->getMessage(), 'stacktrace' => $ex->getTrace()]);
        }

        return false;
    }
}
