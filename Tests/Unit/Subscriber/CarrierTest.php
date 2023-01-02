<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Subscriber;

use Enlight_Controller_Front;
use Enlight_Controller_Request_RequestTestCase;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\OrderProvider;
use SwagPaymentPayPalUnified\Components\ShippingProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\ShippingResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Shipping;
use SwagPaymentPayPalUnified\Subscriber\Carrier;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CarrierTest extends TestCase
{
    /**
     * @return void
     */
    public function testSyncCarrierDoesNothingWithFrontendModule()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setModuleName('frontend');

        $shippingProvider = $this->getMockBuilder(ShippingProvider::class)->disableOriginalConstructor()->getMock();
        $orderProvider = $this->getMockBuilder(OrderProvider::class)->disableOriginalConstructor()->getMock();
        $orderProvider->expects(static::never())->method('getNotSyncedTrackingOrders');

        $front = $this->getMockBuilder(Enlight_Controller_Front::class)->disableOriginalConstructor()->getMock();
        $front->method('Request')->willReturn($request);
        $shippingResource = $this->getMockBuilder(ShippingResource::class)->disableOriginalConstructor()->getMock();

        $logger = $this->getMockForAbstractClass(LoggerServiceInterface::class);
        $settingsService = $this->getMockForAbstractClass(SettingsServiceInterface::class);
        $clientService = $this->getMockBuilder(ClientService::class)->disableOriginalConstructor()->getMock();

        $containerInterface = $this->getMockForAbstractClass(ContainerInterface::class);

        $carrier = new Carrier(
            $shippingProvider,
            $orderProvider,
            $front,
            $shippingResource,
            $logger,
            $settingsService,
            $clientService,
            $containerInterface
        );

        $carrier->syncCarrier();
    }

    /**
     * @return void
     */
    public function testSyncCarrierWillSyncTwentyOneEntries()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setModuleName('backend');

        $shippingProvider = $this->getMockBuilder(ShippingProvider::class)->disableOriginalConstructor()->getMock();
        $orderProvider = $this->getMockBuilder(OrderProvider::class)->disableOriginalConstructor()->getMock();
        $orderProvider->expects(static::once())->method('getNotSyncedTrackingOrders')->willReturn(
            [
                1 => [
                    ['id' => 1, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 1],
                    ['id' => 2, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 1],
                    ['id' => 3, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 1],
                    ['id' => 4, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 1],
                    ['id' => 5, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 1],
                    ['id' => 6, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 1],
                    ['id' => 7, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 1],
                    ['id' => 8, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 1],
                    ['id' => 9, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 1],
                ],
                2 => [
                    ['id' => 10, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 1],
                    ['id' => 11, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar,foo', 'shopId' => 2],
                    ['id' => 13, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 14, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 15, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 16, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 17, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 18, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 19, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 20, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 21, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 22, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 23, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 24, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 25, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 26, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 27, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 28, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 29, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 30, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 31, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 32, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 33, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 34, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                    ['id' => 35, 'transactionID' => 'foo', 'carrier' => 'DHL', 'trackingCode' => 'bar', 'shopId' => 2],
                ],
            ]
        );

        $orderProvider->expects(static::exactly(3))->method('setPaypalCarrierSent');

        $front = $this->getMockBuilder(Enlight_Controller_Front::class)->disableOriginalConstructor()->getMock();
        $front->method('Request')->willReturn($request);
        $shippingResource = $this->getMockBuilder(ShippingResource::class)->disableOriginalConstructor()->getMock();
        $shippingResource->method('batch')->with(
            static::callback(
                function (Shipping $shipping) {
                    foreach ($shipping->getTrackers() as $tracker) {
                        $this->assertEquals('DHL', $tracker->getCarrier());
                        $this->assertTrue(\in_array($tracker->getTrackingNumber(), ['bar', 'foo']));
                    }

                    return true;
                }
            )
        )->willReturn(true, true, true);

        $logger = $this->getMockForAbstractClass(LoggerServiceInterface::class);
        $settingsService = $this->getMockForAbstractClass(SettingsServiceInterface::class);
        $settingsService->method('getSettings')->willReturn(new MockSettings());
        $clientService = $this->getMockBuilder(ClientService::class)->disableOriginalConstructor()->getMock();

        $containerInterface = $this->getMockForAbstractClass(ContainerInterface::class);

        $carrier = new Carrier(
            $shippingProvider,
            $orderProvider,
            $front,
            $shippingResource,
            $logger,
            $settingsService,
            $clientService,
            $containerInterface
        );

        $carrier->syncCarrier();
    }
}

class MockSettings
{
    /**
     * @return array{}
     */
    public function toArray()
    {
        return [];
    }
}
