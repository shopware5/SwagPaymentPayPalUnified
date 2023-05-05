<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Widgets;

use Enlight_Class;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacadeInterface;
use SwagPaymentPayPalUnified\Components\Services\ExpressCheckout\PatchOrderService;
use SwagPaymentPayPalUnified\Components\Services\LoggerService;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderPurchaseUnitShippingPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

require_once __DIR__ . '/../../../../Controllers/Widgets/PaypalUnifiedV2ExpressCheckout.php';

class PaypalUnifiedV2ExpressCheckoutTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;
    use DatabaseTestCaseTrait;
    use ReflectionHelperTrait;

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
        $basketHelper = $this->getContainer()->get('shopware.cart.basket_helper', ContainerInterface::NULL_ON_INVALID_REFERENCE);

        $controller = $this->getController(
            Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout::class,
            [
                self::SERVICE_DEPENDENCY_PROVIDER => $this->getContainer()->get('paypal_unified.dependency_provider'),
                self::SERVICE_SHOP => $this->getContainer()->get('shop'),
                self::SERVICE_BASKET_HELPER_CLASS => $basketHelper,
                self::SERVICE_BASKET_HELPER_ID => $basketHelper,
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
     * @return void
     */
    public function testCreateOrderActionRiskManagementShouldFail()
    {
        $sql = 'INSERT INTO `s_core_rulesets` (`id`, `paymentID`, `rule1`, `value1`, `rule2`, `value2`) VALUES
                (5,	7,	"ARTICLESFROM",	"9",	"",	"");';

        $session = $this->getContainer()->get('session');
        $session->offsetSet('sessionId', 'testSessionId');

        $this->getContainer()->get('dbal_connection')->exec($sql);
        $basket = $this->getContainer()->get('modules')->Basket();

        $basket->sAddArticle('SW10239'); // Spachtelmasse
        $basket->sAddArticle('SW10142'); // Herrenhandschuh aus Peccary Leder

        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $controller = Enlight_Class::Instance(
            Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout::class,
            [$request, $response]
        );

        static::assertInstanceOf(Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout::class, $controller);

        $controller->setContainer($this->getContainer());
        $controller->setFront($this->getContainer()->get('front'));
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));
        $controller->preDispatch();

        $controller->createOrderAction();

        $session->offsetUnset('sessionId');

        static::assertTrue($controller->View()->getAssign('riskManagementFailed'));
    }

    /**
     * @return void
     */
    public function testPatchAddressActionExpectEarlyReturnBecauseTokenIsNotGiven()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $controller = Enlight_Class::Instance(
            Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout::class,
            [$request, $response]
        );

        static::assertInstanceOf(Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout::class, $controller);

        $controller->setRequest($request);
        $controller->setResponse($response);

        $loggerMock = $this->createMock(LoggerService::class);
        $loggerMock->expects(static::once())->method('warning');

        $reflectionProperty = $this->getReflectionProperty(Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout::class, 'logger');
        $reflectionProperty->setValue($controller, $loggerMock);

        $controller->patchAddressAction();
    }

    /**
     * @return void
     */
    public function testPatchAddressAction()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $request->setParam('token', 'anyToken');

        $controller = Enlight_Class::Instance(
            Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout::class,
            [$request, $response]
        );

        static::assertInstanceOf(Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout::class, $controller);

        $controller->setContainer($this->getContainer());
        $controller->setFront($this->getContainer()->get('front'));
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));
        $controller->preDispatch();

        $patchOrderServiceMock = $this->createMock(PatchOrderService::class);
        $patchOrderServiceMock->expects(static::once())->method('createExpressShippingAddressPatch')->willReturn(new OrderPurchaseUnitShippingPatch());
        $patchOrderServiceMock->expects(static::once())->method('patchPayPalExpressOrder');

        $reflectionProperty = $this->getReflectionProperty(Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout::class, 'patchOrderService');
        $reflectionProperty->setValue($controller, $patchOrderServiceMock);

        $controller->patchAddressAction();
    }

    /**
     * @return PayPalOrderParameterFacadeInterface
     */
    private function createOrderParameterFacade()
    {
        $facade = $this->createMock(PayPalOrderParameterFacadeInterface::class);
        $payPalOrderParameter = new PayPalOrderParameter([], [], PaymentType::PAYPAL_EXPRESS_V2, null, null, 'anyOrderNumber');
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
