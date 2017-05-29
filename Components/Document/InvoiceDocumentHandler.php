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

namespace SwagPaymentPayPalUnified\Components\Document;

use Doctrine\DBAL\Connection;
use Shopware_Components_Document as Document;
use SwagPaymentPayPalUnified\Components\Services\PaymentInstructionService;

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
     * @param PaymentInstructionService $instructionService
     * @param Connection                $dbalConnection
     */
    public function __construct(
        PaymentInstructionService $instructionService,
        Connection $dbalConnection
    ) {
        $this->instructionService = $instructionService;
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * @param int      $orderNumber
     * @param Document $document
     */
    public function handleDocument($orderNumber, Document $document)
    {
        //Collect all available containers in order to work with some of them later.
        $templateContainers = $document->_view->getTemplateVars('Containers');
        $view = $document->_view;

        //Get the new footer for the document and replace the original one
        $rawFooter = $this->getInvoiceContainer($templateContainers, $view->getTemplateVars('Order'));
        $templateContainers['PayPal_Unified_Instructions_Content']['value'] = $rawFooter['value'];

        $view->assign('Containers', $templateContainers);

        $instructions = $this->instructionService->getInstructions($orderNumber);
        $document->_template->assign('instruction', $instructions->toArray());

        //Reassign the complete template including the new variables.
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
        $translationComp = new \Shopware_Components_Translation();
        $translation = $translationComp->read($orderData['_order']['language'], 'documents', $footer['id']);

        $query = 'SELECT * FROM s_core_documents_box WHERE id = ?';

        $rawFooter = $this->dbalConnection->fetchAssoc($query, [$footer['id']]);

        if (!empty($translation[1]['PayPal_Unified_Instructions_Content_Value'])) {
            $rawFooter['value'] = $translation[1]['PayPal_Unified_Instructions_Content_Value'];
        }

        return $rawFooter;
    }
}
