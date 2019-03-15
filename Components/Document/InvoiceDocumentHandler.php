<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Document;

use Doctrine\DBAL\Connection;
use Shopware_Components_Document as Document;
use Shopware_Components_Snippet_Manager as SnippetManager;
use SwagPaymentPayPalUnified\Components\Services\Plus\PaymentInstructionService;

class InvoiceDocumentHandler
{
    /**
     * @var PaymentInstructionService
     */
    private $instructionService;

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var SnippetManager
     */
    private $snippetManager;

    public function __construct(
        PaymentInstructionService $instructionService,
        Connection $dbalConnection,
        SnippetManager $snippetManager
    ) {
        $this->instructionService = $instructionService;
        $this->dbalConnection = $dbalConnection;
        $this->snippetManager = $snippetManager;
    }

    /**
     * @param int $orderNumber
     */
    public function handleDocument($orderNumber, Document $document)
    {
        //Collect all available containers in order to work with some of them later.
        /** @var array $templateContainers */
        $templateContainers = $document->_view->getTemplateVars('Containers');
        /** @var \Smarty_Data $view */
        $view = $document->_view;
        /** @var array $orderData */
        $orderData = $view->getTemplateVars('Order');
        $orderData = $this->overwritePaymentName($orderData);

        //Get the new footer for the document and replace the original one
        $rawFooter = $this->getInvoiceContainer($templateContainers, $orderData);
        $templateContainers['PayPal_Unified_Instructions_Content']['value'] = $rawFooter['value'];

        $view->assign('Order', $orderData);
        $view->assign('Containers', $templateContainers);

        $instructions = $this->instructionService->getInstructions($orderNumber);
        if ($instructions) {
            $document->_template->assign('PayPalUnifiedInvoiceInstruction', $instructions->toArray());
        }

        //Reassign the complete template including the new variables.
        /** @var array $containerData */
        $containerData = $view->getTemplateVars('Containers');
        $containerData['Footer'] = $containerData['PayPal_Unified_Instructions_Footer'];
        $containerData['Content_Info'] = $containerData['PayPal_Unified_Instructions_Content'];
        $containerData['Content_Info']['value'] = $document->_template->fetch('string:' . $containerData['Content_Info']['value']);
        $containerData['Content_Info']['style'] = '}' . $containerData['Content_Info']['style'] . ' #info {';

        $view->assign('Containers', $containerData);
    }

    /**
     * @param array $containers
     * @param array $orderData
     *
     * @return array
     */
    public function getInvoiceContainer($containers, $orderData)
    {
        $footer = $containers['PayPal_Unified_Instructions_Content'];
        $translation = (new \Shopware_Components_Translation())->read(
            $orderData['_order']['language'],
            'documents',
            $footer['id']
        );

        $query = 'SELECT * FROM s_core_documents_box WHERE id = ?';

        $rawFooter = $this->dbalConnection->fetchAssoc($query, [$footer['id']]);

        if (!empty($translation[1]['PayPal_Unified_Instructions_Content_Value'])) {
            $rawFooter['value'] = $translation[1]['PayPal_Unified_Instructions_Content_Value'];
        }

        return $rawFooter;
    }

    /**
     * @return array
     */
    private function overwritePaymentName(array $orderData)
    {
        $invoicePaymentName = $this->snippetManager->getNamespace('frontend/paypal_unified/checkout/finish')->get('paymentName/PayPalPlusInvoice');
        $orderData['_payment']['description'] = $invoicePaymentName;

        return $orderData;
    }
}
