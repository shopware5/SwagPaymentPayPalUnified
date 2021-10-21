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
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Capture;

class OrderResource
{
    /**
     * @var ClientService
     */
    private $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function get($id)
    {
        return $this->clientService->sendRequest(RequestType::GET, sprintf('%s/%s', RequestUri::ORDER_RESOURCE, $id));
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function capture($id, Capture $capture)
    {
        $requestData = $capture->toArray();

        return $this->clientService->sendRequest(
            RequestType::POST,
            sprintf('%s/%s/capture', RequestUri::ORDER_RESOURCE, $id),
            $requestData
        );
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function void($id)
    {
        return $this->clientService->sendRequest(
            RequestType::POST,
            sprintf('%s/%s/do-void', RequestUri::ORDER_RESOURCE, $id)
        );
    }
}
