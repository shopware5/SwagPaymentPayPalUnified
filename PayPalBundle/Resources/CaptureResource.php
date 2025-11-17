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
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\CaptureRefund;

class CaptureResource
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
     * @param string $id
     *
     * @return array
     */
    public function get($id)
    {
        $this->logger->debug(\sprintf('%s GET WITH ID %s', __METHOD__, $id));

        return $this->clientService->sendRequest(RequestType::GET, \sprintf('%s/%s', RequestUri::CAPTURE_RESOURCE, $id));
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function refund($id, CaptureRefund $refund)
    {
        $this->logger->debug(\sprintf('%s REFUND WITH ID %s', __METHOD__, $id), $refund->toArray());

        $requestData = $refund->toArray();

        return $this->clientService->sendRequest(
            RequestType::POST,
            \sprintf('%s/%s/refund', RequestUri::CAPTURE_RESOURCE, $id),
            $requestData
        );
    }
}
