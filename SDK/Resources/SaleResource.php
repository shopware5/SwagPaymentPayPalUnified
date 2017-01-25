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

namespace SwagPaymentPayPalUnified\SDK\Resources;

use SwagPaymentPayPalUnified\SDK\RequestType;
use SwagPaymentPayPalUnified\SDK\RequestUri;
use SwagPaymentPayPalUnified\SDK\Services\ClientService;
use SwagPaymentPayPalUnified\SDK\Structs\Payment\Transactions\Amount;

class SaleResource
{
    /**
     * @var ClientService $clientService
     */
    private $clientService;

    /**
     * @param ClientService $clientService
     */
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * @param string $saleId
     * @return array
     */
    public function get($saleId)
    {
        return $this->clientService->sendRequest(RequestType::GET, RequestUri::SALE_RESOURCE . '/' . $saleId);
    }

    /**
     * @param string $saleId
     * @param Amount $amount
     * @param string $invoiceNumber
     * @return array
     */
    public function refund($saleId, Amount $amount = null, $invoiceNumber = '')
    {
        $requestData = [];

        if ($amount !== null) {
            $requestData['amount'] = $amount->toArray();
        }

        $requestData['invoice_number'] = $invoiceNumber;

        return $this->clientService->sendRequest(RequestType::POST, RequestUri::SALE_RESOURCE . '/' . $saleId . '/refund', $requestData);
    }
}
