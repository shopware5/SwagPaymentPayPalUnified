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

class DocumentTemplateService
{
    /** @var Connection $dbalConnection */
    private $dbalConnection;

    /**
     * @param Connection $dbalConnection
     */
    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
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
