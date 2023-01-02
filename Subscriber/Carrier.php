<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Front;
use Enlight_Controller_Request_Request;
use Enlight_Event_EventArgs;
use RuntimeException;
use SwagPaymentPayPalUnified\Components\OrderProvider;
use SwagPaymentPayPalUnified\Components\ShippingProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\ShippingResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Shipping;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Shipping\Tracker;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Throwable;

class Carrier implements SubscriberInterface
{
    const FALLBACK_DISPATCH_ID = '0';

    const TRACKING_CODE_DELIMITER = ',';

    /**
     * @var ShippingProvider
     */
    private $shippingProvider;

    /**
     * @var OrderProvider
     */
    private $orderProvider;

    /**
     * @var Enlight_Controller_Front
     */
    private $front;

    /**
     * @var ShippingResource
     */
    private $shippingResource;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @var bool
     */
    private $enabled;

    public function __construct(
        ShippingProvider $shippingProvider,
        OrderProvider $orderProvider,
        Enlight_Controller_Front $front,
        ShippingResource $shippingResource,
        LoggerServiceInterface $logger,
        SettingsServiceInterface $settingsService,
        ClientService $clientService,
        ContainerInterface $container
    ) {
        $this->shippingProvider = $shippingProvider;
        $this->orderProvider = $orderProvider;
        $this->front = $front;
        $this->shippingResource = $shippingResource;
        $this->logger = $logger;
        $this->settingsService = $settingsService;
        $this->clientService = $clientService;
        $this->enabled = $container->hasParameter('shopware.plugin.paypal.tracking.enabled') ? $container->getParameter('shopware.plugin.paypal.tracking.enabled') : true;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Order_SaveOrder_FilterAttributes' => 'onFilterOrderAttributes',
            'Shopware\Models\Order\Order::postUpdate' => 'syncCarrier',
            'Shopware\Models\Order\Order::postPersist' => 'syncCarrier',
        ];
    }

    /**
     * @return void
     */
    public function onFilterOrderAttributes(Enlight_Event_EventArgs $args)
    {
        if (!$this->enabled) {
            return;
        }

        $dispatchId = $args->get('orderParams')['dispatchID'];

        if ($dispatchId === self::FALLBACK_DISPATCH_ID) {
            return;
        }

        $carrier = $this->shippingProvider->getCarrierByShippingId($dispatchId);

        if (!\is_string($carrier) || $carrier === '') {
            return;
        }

        $attributes = $args->getReturn();
        $attributes['swag_paypal_unified_carrier'] = $carrier;

        $this->logger->debug(sprintf('%s CARRIER %s FOR SHIPPING METHODE %s IS USED FOR ORDER %s', __METHOD__, $carrier, (string) $dispatchId, $args->get('orderParams')['ordernumber']));

        $args->setReturn($attributes);
    }

    /**
     * @return void
     */
    public function syncCarrier()
    {
        if (!$this->enabled) {
            return;
        }

        $request = $this->front->Request();

        if (!$request instanceof Enlight_Controller_Request_Request) {
            return;
        }

        if (!\in_array($request->getModuleName(), ['backend', 'api'])) {
            return;
        }

        $shopOrders = $this->orderProvider->getNotSyncedTrackingOrders();
        if (empty($shopOrders)) {
            return;
        }

        $this->logger->debug(sprintf('%s START ORDERS WITH CARRIERS SYNCING OF %s', __METHOD__, implode(' ', array_column($shopOrders, 'id'))));

        foreach ($shopOrders as $shopId => $orders) {
            $settings = $this->settingsService->getSettings($shopId);
            if ($settings === null) {
                $this->logger->debug(sprintf('%s SETTINGS ARE NOT AVAILABLE FOR SHOP %s', __METHOD__, $shopId));
                throw new RuntimeException(sprintf('Settings are not available for shop %s', $shopId));
            }
            $this->clientService->configure($settings->toArray());

            $trackers = [];
            foreach ($orders as $order) {
                foreach ($this->getTrackerFromOrder($order) as $tracker) {
                    $trackers[sprintf('%s_%s', $order['id'], $tracker->getTrackingNumber())] = $tracker;
                }

                // Only send 3 orders to support multiple Transaction numbers per order
                if (\count($trackers) % 20 === 0) {
                    $this->sendTrackingData($trackers);
                    $trackers = [];
                }
            }

            if (empty($trackers)) {
                continue;
            }

            $this->sendTrackingData($trackers);
        }
    }

    /**
     * @param array{id: string, transactionID: string, trackingCode: string, status: string, carrier: string, shopId: string} $order
     *
     * @return Tracker[]
     */
    private function getTrackerFromOrder(array $order)
    {
        $trackers = [];

        foreach (explode(self::TRACKING_CODE_DELIMITER, $order['trackingCode']) as $multiTrackingCode) {
            $tracker = new Tracker();
            $tracker->setTransactionId($order['transactionID']);
            $tracker->setCarrier($order['carrier']);
            $tracker->setStatus(Tracker::STATUS_SHIPPED);
            $tracker->setTrackingNumber($multiTrackingCode);
            $trackers[] = $tracker;
        }

        return $trackers;
    }

    /**
     * @param Tracker[] $trackers
     *
     * @return void
     */
    private function sendTrackingData(array $trackers)
    {
        $orderIds = [];
        foreach (array_keys($trackers) as $keys) {
            $orderIds[] = explode('_', $keys)[0];
        }

        $shipping = new Shipping();
        $shipping->setTrackers(array_values($trackers));

        $this->logger->debug(sprintf('%s ORDERS %s ARE SYNCED', __METHOD__, implode(' ', array_keys($trackers))));
        try {
            $this->shippingResource->batch($shipping);
        } catch (Throwable $e) {
            $this->logger->debug(sprintf('%s TRACKERS FOR ORDERS %s COULD NOT BE SYNCED', __METHOD__, implode(' ', array_values($orderIds))));

            return;
        }

        $orderIds = [];
        foreach (array_keys($trackers) as $keys) {
            $orderIds[] = explode('_', $keys)[0];
        }

        $this->orderProvider->setPaypalCarrierSent($orderIds);
    }
}
