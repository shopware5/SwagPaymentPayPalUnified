<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber\Documents;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Hook_HookArgs as HookArgs;
use SwagPaymentPayPalUnified\Components\Document\InstallmentsDocumentHandler;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\Installments\OrderCreditInfoService;

class Installments implements SubscriberInterface
{
    /**
     * @var OrderCreditInfoService
     */
    private $creditInfoService;

    /**
     * @var Connection
     */
    private $dbalConnection;

    public function __construct(
        OrderCreditInfoService $creditInfoService,
        Connection $dbalConnection
    ) {
        $this->dbalConnection = $dbalConnection;
        $this->creditInfoService = $creditInfoService;
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

    public function onBeforeRenderDocument(HookArgs $args)
    {
        /** @var \Shopware_Components_Document $document */
        $document = $args->getSubject();

        if (!$document) {
            return;
        }

        $paymentMethodProvider = new PaymentMethodProvider();
        $installmentsPaymentId = $paymentMethodProvider->getPaymentId($this->dbalConnection, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);
        $orderPaymentMethodId = (int) $document->_order->payment['id'];

        //This order has not been payed with paypal unified.
        if ($orderPaymentMethodId !== $installmentsPaymentId) {
            return;
        }

        $orderNumber = $document->_order->order['ordernumber'];

        $documentHandler = new InstallmentsDocumentHandler($this->dbalConnection, $this->creditInfoService);
        $documentHandler->handleDocument($orderNumber, $document);
    }
}
