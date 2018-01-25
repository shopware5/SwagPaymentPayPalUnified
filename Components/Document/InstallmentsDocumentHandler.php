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

        if ($creditInfo) {
            $view->assign('paypalInstallmentsCredit', $creditInfo->toArray());
        }
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
