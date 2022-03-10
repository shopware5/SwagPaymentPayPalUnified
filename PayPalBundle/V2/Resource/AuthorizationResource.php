<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Resource;

use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\RequestUriV2;

class AuthorizationResource
{
    /**
     * @var ClientService
     */
    private $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
        $this->clientService->setPartnerAttributionId(PartnerAttributionId::PAYPAL_ALL_V2);
    }

    /**
     * @param string $authorizationId
     *
     * @return Authorization
     */
    public function get($authorizationId)
    {
        $response = $this->clientService->sendRequest(
            RequestType::GET,
            \sprintf('%s/%s', RequestUriV2::AUTHORIZATIONS_RESOURCE, $authorizationId)
        );

        return (new Authorization())->assign($response);
    }

    /**
     * @param string $authorizationId
     * @param bool   $minimalResponse
     *
     * @return Capture
     */
    public function capture(
        $authorizationId,
        Capture $capture,
        $minimalResponse = true
    ) {
        if ($minimalResponse === false) {
            $this->clientService->setHeader('Prefer', 'return=representation');
        }

        $response = $this->clientService->sendRequest(
            RequestType::POST,
            \sprintf('%s/%s/capture', RequestUriV2::AUTHORIZATIONS_RESOURCE, $authorizationId),
            $capture->toArray()
        );

        return $capture->assign($response);
    }

    /**
     * @param string $authorizationId
     *
     * @return void
     */
    public function void($authorizationId)
    {
        $this->clientService->sendRequest(
            RequestType::POST,
            \sprintf('%s/%s/void', RequestUriV2::AUTHORIZATIONS_RESOURCE, $authorizationId),
            null
        );
    }
}
