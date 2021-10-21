<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Resource;

use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Refund;
use SwagPaymentPayPalUnified\PayPalBundle\V2\RequestUriV2;

class CaptureResource
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
     * @param string $captureId
     *
     * @return Capture
     */
    public function get($captureId)
    {
        $response = $this->clientService->sendRequest(
            RequestType::GET,
            \sprintf('%s/%s', RequestUriV2::CAPTURES_RESOURCE, $captureId)
        );

        return (new Capture())->assign($response);
    }

    /**
     * @param string $captureId
     * @param string $partnerAttributionId
     * @param bool   $minimalResponse
     *
     * @return Refund
     */
    public function refund(
        $captureId,
        Refund $refund,
        $partnerAttributionId,
        $minimalResponse = true
    ) {
        $this->clientService->setPartnerAttributionId($partnerAttributionId);
        if ($minimalResponse === false) {
            $this->clientService->setHeader('Prefer', 'return=representation');
        }

        $response = $this->clientService->sendRequest(
            RequestType::POST,
            \sprintf('%s/%s/refund', RequestUriV2::CAPTURES_RESOURCE, $captureId),
            $refund->toArray()
        );

        return $refund->assign($response);
    }
}
