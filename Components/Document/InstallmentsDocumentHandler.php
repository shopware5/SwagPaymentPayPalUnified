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
use SwagPaymentPayPalUnified\Components\Services\Installments\OrderCreditInfoService;
use SwagPaymentPayPalUnified\Models\FinancingInformation;

class InstallmentsDocumentHandler
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var OrderCreditInfoService
     */
    private $creditInfoService;

    /**
     * @param Connection             $dbalConnection
     * @param OrderCreditInfoService $creditInfoService
     */
    public function __construct(Connection $dbalConnection, OrderCreditInfoService $creditInfoService)
    {
        $this->dbalConnection = $dbalConnection;
        $this->creditInfoService = $creditInfoService;
    }

    /**
     * @param int      $orderNumber
     * @param Document $document
     */
    public function handleDocument($orderNumber, Document $document)
    {
        $view = $document->_view;
        $creditInfo = $this->getCreditInformation($orderNumber);

        $view->assign('paypalInstallmentsCredit', $creditInfo->toArray());
    }

    /**
     * @param $orderNumber
     *
     * @return null|FinancingInformation
     */
    private function getCreditInformation($orderNumber)
    {
        $sql = 'SELECT temporaryID from s_order WHERE ordernumber=:orderNumber';

        $result = $this->dbalConnection->fetchColumn($sql, [
            ':orderNumber' => $orderNumber,
        ]);

        return $this->creditInfoService->getCreditInfo($result);
    }
}
