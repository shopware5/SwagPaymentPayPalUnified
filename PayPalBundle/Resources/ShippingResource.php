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
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Shipping;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Shipping\Tracker;

class ShippingResource
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
     * @return void
     */
    public function batch(Shipping $shippingBatch)
    {
        $this->clientService->sendRequest(
            RequestType::POST,
            \sprintf('%s/trackers-batch', RequestUri::SHIPPING_RESOURCE),
            $shippingBatch->toArray()
        );
    }

    /**
     * @return void
     */
    public function update(Tracker $tracker)
    {
        $this->clientService->sendRequest(
            RequestType::PUT,
            \sprintf('%s/trackers/%s-%s', RequestUri::SHIPPING_RESOURCE, $tracker->getTransactionId(), $tracker->getTrackingNumber()),
            $tracker->toArray()
        );
    }
}
