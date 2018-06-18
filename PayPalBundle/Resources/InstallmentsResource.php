<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Resources;

use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\RequestUri;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingRequest;

class InstallmentsResource
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
     * @param FinancingRequest $financingRequest
     *
     * @return array
     */
    public function getFinancingOptions(FinancingRequest $financingRequest)
    {
        $this->clientService->setPartnerAttributionId(PartnerAttributionId::PAYPAL_INSTALLMENTS);

        return $this->clientService->sendRequest(
            RequestType::POST,
            RequestUri::FINANCING_RESOURCE,
            $financingRequest->toArray()
        );
    }
}
