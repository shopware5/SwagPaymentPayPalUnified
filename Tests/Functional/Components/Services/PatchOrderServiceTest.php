<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacadeInterface;
use SwagPaymentPayPalUnified\Components\Services\ExpressCheckout\PatchOrderService;
use SwagPaymentPayPalUnified\Components\Services\LoggerService;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping\Address;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderPurchaseUnitShippingPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class PatchOrderServiceTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;

    /**
     * @return void
     */
    public function testCreateExpressShippingAddressPatchExpectsNullNoPurchaseUnit()
    {
        $orderFactoryMock = $this->createMock(OrderFactory::class);
        $orderFactoryMock->method('createOrder')->willReturn(new Order());

        $patchOrderService = $this->createPatchOrderService(null, $orderFactoryMock);

        $result = $patchOrderService->createExpressShippingAddressPatch([]);

        static::assertNull($result);
    }

    /**
     * @return void
     */
    public function testCreateExpressShippingAddressPatchExpectsNullNoShipping()
    {
        $order = new Order();
        $purchaseUnit = new PurchaseUnit();
        $order->setPurchaseUnits([$purchaseUnit]);

        $orderFactoryMock = $this->createMock(OrderFactory::class);
        $orderFactoryMock->method('createOrder')->willReturn($order);

        $patchOrderService = $this->createPatchOrderService(null, $orderFactoryMock);

        $result = $patchOrderService->createExpressShippingAddressPatch([]);

        static::assertNull($result);
    }

    /**
     * @return void
     */
    public function testCreateExpressShippingAddressPatchExpectsNullNoAddress()
    {
        $order = new Order();
        $purchaseUnit = new PurchaseUnit();
        $shipping = new Shipping();
        $purchaseUnit->setShipping($shipping);
        $order->setPurchaseUnits([$purchaseUnit]);

        $orderFactoryMock = $this->createMock(OrderFactory::class);
        $orderFactoryMock->method('createOrder')->willReturn($order);

        $patchOrderService = $this->createPatchOrderService(null, $orderFactoryMock);

        $result = $patchOrderService->createExpressShippingAddressPatch([]);

        static::assertNull($result);
    }

    /**
     * @return void
     */
    public function testCreateExpressShippingAddressPatch()
    {
        $order = new Order();
        $purchaseUnit = new PurchaseUnit();
        $shipping = new Shipping();
        $address = new Address();
        $address->setAddressLine1('AddressLine1');
        $address->setAddressLine2('AddressLine2');
        $address->setAdminArea1('AdminArea1');
        $address->setAdminArea2('AdminArea2');
        $address->setCountryCode('EN');
        $address->setPostalCode('12345');
        $shipping->setAddress($address);
        $purchaseUnit->setShipping($shipping);
        $order->setPurchaseUnits([$purchaseUnit]);

        $orderFactoryMock = $this->createMock(OrderFactory::class);
        $orderFactoryMock->method('createOrder')->willReturn($order);

        $patchOrderService = $this->createPatchOrderService(null, $orderFactoryMock);

        $result = $patchOrderService->createExpressShippingAddressPatch([]);

        static::assertInstanceOf(OrderPurchaseUnitShippingPatch::class, $result);
        static::assertSame(OrderPurchaseUnitShippingPatch::PATH, $result->getPath());
        static::assertSame(Patch::OPERATION_REPLACE, $result->getOp());
        static::assertTrue(\is_array($result->getValue()));
        static::assertSame('AddressLine1', $result->getValue()['address_line_1']);
        static::assertSame('AddressLine2', $result->getValue()['address_line_2']);
        static::assertSame('AdminArea2', $result->getValue()['admin_area_2']);
        static::assertSame('AdminArea1', $result->getValue()['admin_area_1']);
        static::assertSame('12345', $result->getValue()['postal_code']);
        static::assertSame('EN', $result->getValue()['country_code']);
    }

    /**
     * @param PayPalOrderParameterFacadeInterface|null $orderParameterFacade
     * @param OrderFactory|null                        $orderFactory
     * @param OrderResource|null                       $orderResource
     * @param LoggerService|null                       $loggerService
     *
     * @return PatchOrderService
     */
    private function createPatchOrderService(
        $orderParameterFacade = null,
        $orderFactory = null,
        $orderResource = null,
        $loggerService = null
    ) {
        return new PatchOrderService(
            $orderParameterFacade ?: $this->getContainer()->get('paypal_unified.paypal_order_parameter_facade'),
            $orderFactory ?: $this->getContainer()->get('paypal_unified.order_factory'),
            $orderResource ?: $this->getContainer()->get('paypal_unified.v2.order_resource'),
            $loggerService ?: $this->createMock(LoggerService::class)
        );
    }
}
