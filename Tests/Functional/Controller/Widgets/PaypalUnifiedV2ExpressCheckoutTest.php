<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Widgets;

use Enlight_Controller_Request_RequestTestCase;
use Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacadeInterface;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

require_once __DIR__ . '/../../../../Controllers/Widgets/PaypalUnifiedV2ExpressCheckout.php';

class PaypalUnifiedV2ExpressCheckoutTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;

    const DEFAULT_SHIPPING_METHOD_ID = 9;

    /**
     * @return void
     */
    public function testCreateOrderAction()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('addProduct', true);
        $request->setParam('productNumber', 'SW10178');
        $request->setParam('productQuantity', 1);

        $order = $this->createPayPalOrder();

        $controller = $this->getController(
            Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout::class,
            [
                self::SERVICE_DEPENDENCY_PROVIDER => $this->getContainer()->get('paypal_unified.dependency_provider'),
                self::SERVICE_SHOP => $this->getContainer()->get('shop'),
                self::SERVICE_BASKET_HELPER => $this->getContainer()->get('shopware.cart.basket_helper', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                self::SERVICE_ORDER_PARAMETER_FACADE => $this->createOrderParameterFacade(),
                self::SERVICE_ORDER_FACTORY => $this->createOrderFactory($order),
                self::SERVICE_ORDER_RESOURCE => $this->createOrderResource($order),
                self::SERVICE_DBAL_CONNECTION => $this->getContainer()->get('dbal_connection'),
            ],
            $request
        );

        $this->getContainer()->get('session')->offsetSet('sessionId', 'testSessionId');
        $controller->createOrderAction();

        static::assertSame(self::DEFAULT_SHIPPING_METHOD_ID, $this->getContainer()->get('session')->get('sDispatch'));

        $this->getContainer()->get('modules')->getModule('basket')->sDeleteBasket();
    }

    /**
     * @return PayPalOrderParameterFacadeInterface
     */
    private function createOrderParameterFacade()
    {
        $facade = $this->createMock(PayPalOrderParameterFacadeInterface::class);
        $payPalOrderParameter = new PayPalOrderParameter([], [], PaymentType::PAYPAL_EXPRESS_V2, null, null);
        $facade->method('createPayPalOrderParameter')->willReturn($payPalOrderParameter);

        return $facade;
    }

    /**
     * @return Order
     */
    private function createPayPalOrder()
    {
        $order = new Order();
        $order->setId('test');

        return $order;
    }

    /**
     * @return OrderFactory
     */
    private function createOrderFactory(Order $order)
    {
        $orderFactory = $this->createMock(OrderFactory::class);
        $orderFactory->method('createOrder')->willReturn($order);

        return $orderFactory;
    }

    /**
     * @return OrderResource
     */
    private function createOrderResource(Order $order)
    {
        $orderResource = $this->createMock(OrderResource::class);
        $orderResource->method('create')->willReturn($order);

        return $orderResource;
    }
}
