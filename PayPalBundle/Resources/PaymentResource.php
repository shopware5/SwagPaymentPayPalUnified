<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Resources;

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

    /**
     * @param ClientService $clientService
     */
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * @param Payment $payment
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
     * @return null|array
     */
    public function execute($payerId, $paymentId)
    {
        $requestData = ['payer_id' => $payerId];

        return $this->clientService->sendRequest(
            RequestType::POST,
            RequestUri::PAYMENT_RESOURCE . '/' . $paymentId . '/execute',
            $requestData
        );
    }

    /**
     * @param string $paymentId
     *
     * @return array
     */
    public function get($paymentId)
    {
        return $this->clientService->sendRequest(RequestType::GET, RequestUri::PAYMENT_RESOURCE . '/' . $paymentId);
    }

    /**
     * @param $paymentId
     * @param PatchInterface[] $patches
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
            RequestUri::PAYMENT_RESOURCE . '/' . $paymentId,
            $requestData
        );
    }
}
