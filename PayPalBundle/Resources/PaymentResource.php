<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PatchInterface;
use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\RequestUri;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class PaymentResource
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
     * @throws RequestException
     *
     * @return array
     */
    public function create(Payment $payment)
    {
        return $this->clientService->sendRequest(RequestType::POST, RequestUri::PAYMENT_RESOURCE, $payment->toArray());
    }

    /**
     * @param string $payerId
     * @param string $paymentId
     *
     * @throws RequestException
     *
     * @return array|null
     */
    public function execute($payerId, $paymentId)
    {
        $requestData = ['payer_id' => $payerId];

        return $this->clientService->sendRequest(
            RequestType::POST,
            sprintf('%s/%s/execute', RequestUri::PAYMENT_RESOURCE, $paymentId),
            $requestData
        );
    }

    /**
     * @param string $paymentId
     *
     * @throws RequestException
     *
     * @return array
     */
    public function get($paymentId)
    {
        return $this->clientService->sendRequest(
            RequestType::GET,
            sprintf('%s/%s', RequestUri::PAYMENT_RESOURCE, $paymentId)
        );
    }

    /**
     * @param string           $paymentId
     * @param PatchInterface[] $patches
     *
     * @throws RequestException
     */
    public function patch($paymentId, array $patches)
    {
        $requestData = [];
        foreach ($patches as $patch) {
            $requestData[] = [
                'op' => $patch->getOperation(),
                'path' => $patch->getPath(),
                'value' => $patch->getValue(),
            ];
        }

        $this->clientService->sendRequest(
            RequestType::PATCH,
            sprintf('%s/%s', RequestUri::PAYMENT_RESOURCE, $paymentId),
            $requestData
        );
    }
}
