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
use Enlight_Template_Manager as Template;
use Shopware_Components_Snippet_Manager as SnippetManager;
use Shopware_Components_Translation;
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
     * @var SnippetManager
     */
    private $snippetManager;

    /**
     * @var Shopware_Components_Translation
     */
    private $translation;

    /**
     * @var Template
     */
    private $templateManager;

    public function __construct(
        PaymentInstructionService $paymentInstructionService,
        Connection $dbalConnection,
        SnippetManager $snippetManager,
        Shopware_Components_Translation $translation = null,
        Template $templateManager
    ) {
        $this->paymentInstructionsService = $paymentInstructionService;
        $this->dbalConnection = $dbalConnection;
        $this->snippetManager = $snippetManager;
        $this->translation = $translation;
        $this->templateManager = $templateManager;

        if ($this->translation === null) {
            $this->translation = new Shopware_Components_Translation();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Components_Document::assignValues::after' => 'onBeforeRenderDocument',
            'Shopware_Modules_Order_SendMail_FilterVariables' => 'onFilterMailVariables',
        ];
    }

    public function onBeforeRenderDocument(\Enlight_Hook_HookArgs $args)
    {
        /** @var \Shopware_Components_Document|null $document */
        $document = $args->getSubject();

        if (!$document) {
            return;
        }

        $unifiedPaymentId = (new PaymentMethodProvider())->getPaymentId($this->dbalConnection);

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

        $documentHandler = new InvoiceDocumentHandler(
            $this->paymentInstructionsService,
            $this->dbalConnection,
            $this->snippetManager,
            $this->translation
        );
        $documentHandler->handleDocument($orderNumber, $document);
    }

    public function onFilterMailVariables(\Enlight_Event_EventArgs $eventArgs)
    {
        $vars = $eventArgs->getReturn();

        if ($vars['additional']['payment']['name'] !== 'SwagPaymentPayPalUnified') {
            return $vars;
        }

        $vars['additional']['payment']['additionaldescription'] = $this->templateManager->fetch(
            sprintf('string:%s', $vars['additional']['payment']['additionaldescription'])
        );

        return $vars;
    }
}
