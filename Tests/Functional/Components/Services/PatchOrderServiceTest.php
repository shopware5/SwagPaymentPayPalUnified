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
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderPurchaseUnitPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderPurchaseUnitShippingAddressPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderPurchaseUnitShippingNamePatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class PatchOrderServiceTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use SettingsHelperTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testCreateExpressShippingAddressPatchExpectsNullNoPurchaseUnit()
    {
        $orderFactoryMock = $this->createMock(OrderFactory::class);
        $orderFactoryMock->method('createOrder')->willReturn(new Order());

        $patchOrderService = $this->createPatchOrderService(null, $orderFactoryMock);

        $result = $patchOrderService->createExpressShippingAddressPatch([], $this->getCartData());

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

        $result = $patchOrderService->createExpressShippingAddressPatch([], $this->getCartData());

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

        $result = $patchOrderService->createExpressShippingAddressPatch([], $this->getCartData());

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

        $result = $patchOrderService->createExpressShippingAddressPatch([], $this->getCartData());

        static::assertInstanceOf(OrderPurchaseUnitShippingAddressPatch::class, $result);
        static::assertSame(OrderPurchaseUnitShippingAddressPatch::PATH, $result->getPath());
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
     * @return void
     */
    public function testCreateExpressShippingNamePatchShouldReturnNullNoNameDataIsset()
    {
        $patchOrderService = $this->createPatchOrderService();

        static::assertNull($patchOrderService->createExpressShippingNamePatch([]));
    }

    /**
     * @return void
     */
    public function testCreateExpressShippingNamePatchShouldReturnNullNoFirstNameIsset()
    {
        $patchOrderService = $this->createPatchOrderService();

        $customerData = ['shippingaddress' => ['lastname' => 'Bar']];

        static::assertNull($patchOrderService->createExpressShippingNamePatch($customerData));
    }

    /**
     * @return void
     */
    public function testCreateExpressShippingNamePatchShouldReturnNullNoLastNameIsset()
    {
        $patchOrderService = $this->createPatchOrderService();

        $customerData = ['shippingaddress' => ['firstname' => 'Foo']];

        static::assertNull($patchOrderService->createExpressShippingNamePatch($customerData));
    }

    /**
     * @return void
     */
    public function testCreateExpressShippingNamePatch()
    {
        $patchOrderService = $this->createPatchOrderService();

        $customerData = ['shippingaddress' => ['firstname' => 'Foo', 'lastname' => 'Bar']];

        $result = $patchOrderService->createExpressShippingNamePatch($customerData);

        static::assertInstanceOf(OrderPurchaseUnitShippingNamePatch::class, $result);

        $nameResult = $result->getValue();

        static::assertTrue(\is_array($nameResult));
        static::assertSame('Foo Bar', $nameResult['full_name']);
    }

    /**
     * @return void
     */
    public function testCreateOrderPurchaseUnitPatchShouldReturnNull()
    {
        $this->insertGeneralSettingsFromArray(['active' => true, 'submit_cart' => true]);

        $orderFactoryMock = $this->createMock(OrderFactory::class);
        $orderFactoryMock->method('createOrder')->willReturn(new Order());

        $patchOrderService = $this->createPatchOrderService(null, $orderFactoryMock);

        $userData = require __DIR__ . '/_fixtures/OrderBuilderServiceTestUserData.php';
        $cartData = require __DIR__ . '/_fixtures/OrderBuilderServiceTestBasketData.php';

        static::assertNull($patchOrderService->createOrderPurchaseUnitPatch($userData, $cartData));
    }

    /**
     * @return void
     */
    public function testCreateOrderPurchaseUnitPatch()
    {
        $this->insertGeneralSettingsFromArray(['active' => true, 'submit_cart' => true]);

        $patchOrderService = $this->createPatchOrderService();

        $userData = require __DIR__ . '/_fixtures/OrderBuilderServiceTestUserData.php';
        $cartData = require __DIR__ . '/_fixtures/OrderBuilderServiceTestBasketData.php';

        $result = $patchOrderService->createOrderPurchaseUnitPatch($userData, $cartData);

        static::assertInstanceOf(OrderPurchaseUnitPatch::class, $result);

        $resultValue = $result->getValue();
        static::assertTrue(\is_array($resultValue));
        static::assertFalse(\array_key_exists('payee', $resultValue));
        static::assertFalse(\array_key_exists('description', $resultValue));
        static::assertFalse(\array_key_exists('custom_id', $resultValue));
        static::assertFalse(\array_key_exists('invoice_id', $resultValue));
        static::assertFalse(\array_key_exists('shipping', $resultValue));
        static::assertFalse(\array_key_exists('payments', $resultValue));
        static::assertFalse(\array_key_exists('reference_id', $resultValue));

        static::assertTrue(\array_key_exists('amount', $resultValue));
        static::assertTrue(\array_key_exists('items', $resultValue));
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

    /**
     * @return array<string,mixed>
     */
    private function getCartData()
    {
        return [
            'content' => [
                [
                    'ordernumber' => 'SW12345',
                    'quantity' => '1',
                    'tax_rate' => '19.0',
                    'price' => '10.0',
                ],
            ],
        ];
    }
}
