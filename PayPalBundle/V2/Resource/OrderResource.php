<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Resource;

use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\RequestUriV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderArrayFactory\OrderArrayFactory;

class OrderResource
{
    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @var OrderArrayFactory
     */
    private $arrayFactory;

    /**
     * @var LoggerServiceInterface
     */
    private $loggerService;

    public function __construct(
        ClientService $clientService,
        OrderArrayFactory $arrayFactory,
        LoggerServiceInterface $loggerService
    ) {
        $this->clientService = $clientService;
        $this->clientService->setPartnerAttributionId(PartnerAttributionId::PAYPAL_ALL_V2);
        $this->arrayFactory = $arrayFactory;
        $this->loggerService = $loggerService;
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
        $paypalRequestId = null;

        if ($minimalResponse === false) {
            $this->clientService->setHeader('Prefer', 'return=representation');
        }

        if ($order->getPaymentSource() !== null) {
            $paypalRequestId = bin2hex((string) openssl_random_pseudo_bytes(16));

            $this->clientService->setHeader('PayPal-Request-Id', $paypalRequestId);
        }

        $response = $this->clientService->sendRequest(
            RequestType::POST,
            RequestUriV2::ORDERS_RESOURCE,
            $this->arrayFactory->toArray($order, $paymentType)
        );

        $paypalOrder = (new Order())->assign($response);

        if ($paypalRequestId !== null) {
            $this->loggerService->notify(
                'PayPal order with payment source created',
                [
                    'orderId' => $paypalOrder->getId(),
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
