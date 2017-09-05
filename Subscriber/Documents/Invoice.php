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

namespace SwagPaymentPayPalUnified\Subscriber\Documents;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use SwagPaymentPayPalUnified\Components\Document\InvoiceDocumentHandler;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\Plus\PaymentInstructionService;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

class Invoice implements SubscriberInterface
{
    /**
     * @var PaymentInstructionService
     */
    private $paymentInstructionsService;

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @param PaymentInstructionService $paymentInstructionService
     * @param Connection                $dbalConnection
     */
    public function __construct(
        PaymentInstructionService $paymentInstructionService,
        Connection $dbalConnection
    ) {
        $this->paymentInstructionsService = $paymentInstructionService;
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Components_Document::assignValues::after' => 'onBeforeRenderDocument',
        ];
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     */
    public function onBeforeRenderDocument(\Enlight_Hook_HookArgs $args)
    {
        /** @var \Shopware_Components_Document $document */
        $document = $args->getSubject();

        if (!$document) {
            return;
        }

        $paymentMethodProvider = new PaymentMethodProvider();
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId($this->dbalConnection);

        $orderPaymentMethodId = (int) $document->_order->payment['id'];

        //This order has not been payed with paypal unified.
        if ($orderPaymentMethodId !== $unifiedPaymentId) {
            return;
        }

        $paypalPaymentType = $document->_order->order->attributes['swag_paypal_unified_payment_type'];

        if ($paypalPaymentType !== PaymentType::PAYPAL_INVOICE) {
            return;
        }

        $orderNumber = $document->_order->order['ordernumber'];

        $documentHandler = new InvoiceDocumentHandler($this->paymentInstructionsService, $this->dbalConnection);
        $documentHandler->handleDocument($orderNumber, $document);
    }
}
