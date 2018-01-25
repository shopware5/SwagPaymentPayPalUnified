<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Resources;

use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\RequestUri;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\SaleRefund;

class SaleResource
{
    /**
     * @var ClientService
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
     *
     * @return array
     */
    public function get($saleId)
    {
        return $this->clientService->sendRequest(RequestType::GET, RequestUri::SALE_RESOURCE . '/' . $saleId);
    }

    /**
     * @param string     $saleId
     * @param SaleRefund $refund
     *
     * @return array
     */
    public function refund($saleId, SaleRefund $refund)
    {
        $requestData = $refund->toArray();

        return $this->clientService->sendRequest(RequestType::POST, RequestUri::SALE_RESOURCE . '/' . $saleId . '/refund', $requestData);
    }
}
