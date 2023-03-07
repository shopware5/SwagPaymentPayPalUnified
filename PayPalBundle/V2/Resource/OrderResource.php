<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Resource;

use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Services\RequestIdService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\RequestUriV2;
use SwagPaymentPayPalUnified\Subscriber\FraudNet;

class OrderResource
{
    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @var LoggerServiceInterface
     */
    private $loggerService;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var RequestIdService
     */
    private $requestIdService;

    public function __construct(
        ClientService $clientService,
        LoggerServiceInterface $loggerService,
        DependencyProvider $dependencyProvider,
        RequestIdService $requestIdService
    ) {
        $this->clientService = $clientService;
        $this->clientService->setPartnerAttributionId(PartnerAttributionId::PAYPAL_ALL_V2);
        $this->loggerService = $loggerService;
        $this->dependencyProvider = $dependencyProvider;
        $this->requestIdService = $requestIdService;
    }

    /**
     * @param string $orderId
     *
     * @return Order
     */
    public function get($orderId)
    {
        $response = $this->clientService->sendRequest(
            RequestType::GET,
            \sprintf('%s/%s', RequestUriV2::ORDERS_RESOURCE, $orderId)
        );

        return (new Order())->assign($response);
    }

    /**
     * @param PaymentType::* $paymentType
     * @param bool           $minimalResponse
     *
     * @return Order
     */
    public function create(Order $order, $paymentType, $minimalResponse = true)
    {
        $paypalRequestId = $this->requestIdService->generateNewRequestId();

        if ($minimalResponse === false) {
            $this->clientService->setHeader('Prefer', 'return=representation');
        }

        if ($this->requestIdService->isRequestIdRequired($paymentType)) {
            $paypalRequestId = $this->requestIdService->getRequestIdFromSession();
        }

        $this->clientService->setHeader('PayPal-Request-Id', $paypalRequestId);

        $payPalMetaDataId = $this->dependencyProvider->getSession()->offsetGet(FraudNet::FRAUD_NET_SESSION_KEY);
        if (\is_string($payPalMetaDataId)) {
            $this->clientService->setHeader('PayPal-Client-Metadata-Id', $payPalMetaDataId);
        }

        $response = $this->clientService->sendRequest(
            RequestType::POST,
            RequestUriV2::ORDERS_RESOURCE,
            $order->toArray()
        );

        $paypalOrder = (new Order())->assign($response);

        if ($paypalRequestId !== null) {
            $this->loggerService->notify(
                'PayPal order with payment source created',
                [
                    'token' => $paypalOrder->getId(),
                    'requestId' => $paypalRequestId,
                ]
            );
        }

        return $paypalOrder;
    }

    /**
     * @param Patch[] $patches
     * @param string  $orderId
     *
     * @return void
     */
    public function update(array $patches, $orderId)
    {
        $this->clientService->sendRequest(
            RequestType::PATCH,
            \sprintf('%s/%s', RequestUriV2::ORDERS_RESOURCE, $orderId),
            $patches
        );
    }

    /**
     * @param string $orderId
     * @param bool   $minimalResponse
     *
     * @return Order
     */
    public function capture($orderId, $minimalResponse = true)
    {
        if ($minimalResponse === false) {
            $this->clientService->setHeader('Prefer', 'return=representation');
        }

        $response = $this->clientService->sendRequest(
            RequestType::POST,
            \sprintf('%s/%s/capture', RequestUriV2::ORDERS_RESOURCE, $orderId),
            null
        );

        return (new Order())->assign($response);
    }

    /**
     * @param string $orderId
     * @param bool   $minimalResponse
     *
     * @return Order
     */
    public function authorize($orderId, $minimalResponse = true)
    {
        if ($minimalResponse === false) {
            $this->clientService->setHeader('Prefer', 'return=representation');
        }

        $response = $this->clientService->sendRequest(
            RequestType::POST,
            \sprintf('%s/%s/authorize', RequestUriV2::ORDERS_RESOURCE, $orderId),
            null
        );

        return (new Order())->assign($response);
    }
}
