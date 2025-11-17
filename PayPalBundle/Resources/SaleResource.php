<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Resources;

use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
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
     * @var LoggerServiceInterface
     */
    private $logger;

    public function __construct(ClientService $clientService, LoggerServiceInterface $logger)
    {
        $this->clientService = $clientService;
        $this->logger = $logger;
    }

    /**
     * @param string $saleId
     *
     * @return array
     */
    public function get($saleId)
    {
        $this->logger->debug(\sprintf('%s GET WITH ID %s', __METHOD__, $saleId));

        return $this->clientService->sendRequest(RequestType::GET, \sprintf('%s/%s', RequestUri::SALE_RESOURCE, $saleId));
    }

    /**
     * @param string $saleId
     *
     * @return array
     */
    public function refund($saleId, SaleRefund $refund)
    {
        $this->logger->debug(\sprintf('%s REFUND WITH ID %s', __METHOD__, $saleId), $refund->toArray());

        $requestData = $refund->toArray();

        return $this->clientService->sendRequest(
            RequestType::POST,
            \sprintf('%s/%s/refund', RequestUri::SALE_RESOURCE, $saleId),
            $requestData
        );
    }
}
