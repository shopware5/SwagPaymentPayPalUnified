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
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Capture;

class AuthorizationResource
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

        return $this->clientService->sendRequest(
            RequestType::GET,
            \sprintf('%s/%s', RequestUri::AUTHORIZATION_RESOURCE, $id)
        );
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function void($id)
    {
        $this->logger->debug(\sprintf('%s VOID WITH ID %s', __METHOD__, $id));

        return $this->clientService->sendRequest(
            RequestType::POST,
            \sprintf('%s/%s/void', RequestUri::AUTHORIZATION_RESOURCE, $id)
        );
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function capture($id, Capture $capture)
    {
        $this->logger->debug(\sprintf('%s CAPTURE WITH ID %s', __METHOD__, $id), $capture->toArray());

        $requestData = $capture->toArray();

        return $this->clientService->sendRequest(
            RequestType::POST,
            \sprintf('%s/%s/capture', RequestUri::AUTHORIZATION_RESOURCE, $id),
            $requestData
        );
    }
}
