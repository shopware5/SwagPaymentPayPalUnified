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

class RefundResource
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
     * @param string $refundId
     *
     * @return array
     */
    public function get($refundId)
    {
        $this->logger->debug(\sprintf('%s GET WITH ID %s', __METHOD__, $refundId));

        return $this->clientService->sendRequest(RequestType::GET, \sprintf('%s/%s', RequestUri::REFUND_RESOURCE, $refundId));
    }
}
