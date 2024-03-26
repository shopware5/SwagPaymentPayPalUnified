<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Components_Session_Namespace;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseHttp;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Exception\TimeoutInfoException;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacade;
use SwagPaymentPayPalUnified\Components\Services\DispatchValidation;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\TimeoutRefundService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\AssertLocationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\_mocks\PaypalUnifiedApmMock;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class PaypalUnifiedApmCaptureTimeoutTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;
    use AssertLocationTrait;

    /**
     * @return void
     */
    public function testIndexActionShouldSaveTimeoutInfo()
    {
        $request = $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('token', 'xxxxxxxxxxxxxxxx');

        $dispatchValidationMock = $this->createMock(DispatchValidation::class);
        $dispatchValidationMock->method('isInvalid')->willReturn(false);

        $sessionMock = $this->createMock(Enlight_Components_Session_Namespace::class);
        $sessionMock->method('get')->willReturn([
            'sBasket' => require __DIR__ . '/_fixtures/getBasket_result.php',
            'sUserData' => require __DIR__ . '/_fixtures/getUser_result.php',
        ]);

        $dependencyProvider = $this->createMock(DependencyProvider::class);
        $dependencyProvider->method('getSession')->willReturn($sessionMock);

        $payPalOrderParameterFacadeMock = $this->createMock(PayPalOrderParameterFacade::class);
        $payPalOrderParameterFacadeMock->method('createPayPalOrderParameter')->willReturn(new PayPalOrderParameter([], ['content' => ['1' => 2]], 'giropay', '', '', ''));

        $link = new Order\Link();
        $link->setRel('payer-action');
        $link->setHref('https://api.sandbox.paypal.com/v2/checkout/orders/xxxxxxxxxxxxxxxx');
        $order = new Order();
        $order->setLinks([$link]);

        $orderFactoryMock = $this->createMock(OrderFactory::class);
        $orderFactoryMock->method('createOrder')->willReturn($order);

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('create')->willReturn($order);

        $timeoutRefundServiceMock = $this->createMock(TimeoutRefundService::class);
        $timeoutRefundServiceMock->expects(static::once())
            ->method('saveInfo');

        $controller = $this->getController(
            PaypalUnifiedApmMock::class,
            [
                self::SERVICE_TIMOUT_REFUND_SERVICE => $timeoutRefundServiceMock,
                self::SERVICE_DEPENDENCY_PROVIDER => $dependencyProvider,
                self::SERVICE_DISPATCH_VALIDATION => $dispatchValidationMock,
                self::SERVICE_ORDER_FACTORY => $orderFactoryMock,
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_ORDER_PARAMETER_FACADE => $payPalOrderParameterFacadeMock,
            ],
            $request,
            new Enlight_Controller_Response_ResponseHttp()
        );

        $controller->indexAction();
    }

    /**
     * @return void
     */
    public function testReturnActionShouldRefundAndRedirectToRegisterIndexAction()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('token', 'xxxxxxxxxxxxxxxx');

        $response = new Enlight_Controller_Response_ResponseHttp();

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($this->createDefaultOrder());

        $timeoutRefundServiceMock = $this->createMock(TimeoutRefundService::class);
        $timeoutRefundServiceMock->expects(static::once())->method('refund');
        $timeoutRefundServiceMock->expects(static::once())->method('deleteInfo');

        $controller = $this->getController(
            PaypalUnifiedApmMock::class,
            [
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_TIMOUT_REFUND_SERVICE => $timeoutRefundServiceMock,
                self::SERVICE_ORDER_PROPERTY_HELPER => $this->getContainer()->get('paypal_unified.order_property_helper'),
            ],
            $this->request,
            $response
        );

        $controller->returnAction();
        $this->assertLocationEndsWith($response, 'register/index/paymentApproveTimeout/1');
    }

    /**
     * @return void
     */
    public function testReturnActionWithoutTimeoutInfoShouldRedirectToRegisterIndexAction()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('token', 'xxxxxxxxxxxxxxxx');

        $response = new Enlight_Controller_Response_ResponseHttp();

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($order = $this->createDefaultOrder());

        $loggerMock = $this->createMock(LoggerServiceInterface::class);
        $loggerMock->expects(static::once())->method('error')->with('Timeout information not found for PayPal order with ID: xxxxxxxxxxxxxxxx');

        $timeoutRefundServiceMock = $this->createMock(TimeoutRefundService::class);
        $timeoutRefundServiceMock->expects(static::once())->method('refund')->willThrowException(new TimeoutInfoException('xxxxxxxxxxxxxxxx'));

        $controller = $this->getController(
            PaypalUnifiedApmMock::class,
            [
                self::SERVICE_LOGGER_SERVICE => $loggerMock,
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_TIMOUT_REFUND_SERVICE => $timeoutRefundServiceMock,
                self::SERVICE_ORDER_PROPERTY_HELPER => $this->getContainer()->get('paypal_unified.order_property_helper'),
            ],
            $this->request,
            $response
        );

        $controller->returnAction();
        $this->assertLocationEndsWith($response, 'register/index/paymentApproveTimeout/1');
    }

    /**
     * @dataProvider incompleteOrderProvider
     *
     * @return void
     */
    public function testReturnActionWithIncompleteOrderShouldRedirectToRegisterIndexAction(Order $order)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('token', '123456789');

        $response = new Enlight_Controller_Response_ResponseHttp();

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($order);

        $loggerMock = $this->createMock(LoggerServiceInterface::class);
        $loggerMock->expects(static::once())->method('error')->with('No capture found in order with ID: 123456789');

        $timeoutRefundServiceMock = $this->createMock(TimeoutRefundService::class);

        $controller = $this->getController(
            PaypalUnifiedApmMock::class,
            [
                self::SERVICE_LOGGER_SERVICE => $loggerMock,
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_TIMOUT_REFUND_SERVICE => $timeoutRefundServiceMock,
                self::SERVICE_ORDER_PROPERTY_HELPER => $this->getContainer()->get('paypal_unified.order_property_helper'),
            ],
            $this->request,
            $response
        );

        $controller->returnAction();
        $this->assertLocationEndsWith($response, 'register/index/paymentApproveTimeout/1');
    }

    /**
     * @return array<string, array<int, Order>>
     */
    public function incompleteOrderProvider()
    {
        return [
            'Order without PurchaseUnits' => [$this->createOrderWithoutPurchaseUnits()],
            'Order without Payments' => [$this->createOrderWithoutPayments()],
            'Order without Captures' => [$this->createOrderWithoutCaptures()],
            'Order with empty Captures' => [$this->createOrderWithEmptyCaptures()],
        ];
    }

    /**
     * @return Order
     */
    private function createOrderWithEmptyCaptures()
    {
        $payment = new Order\PurchaseUnit\Payments();
        $payment->setCaptures([]);
        $purchaseUnit = new Order\PurchaseUnit();
        $purchaseUnit->setPayments($payment);
        $order = $this->createOrderWithoutPurchaseUnits();
        $order->setPurchaseUnits([$purchaseUnit]);

        return $order;
    }

    /**
     * @return Order
     */
    private function createOrderWithoutCaptures()
    {
        $payment = new Order\PurchaseUnit\Payments();
        $purchaseUnit = new Order\PurchaseUnit();
        $purchaseUnit->setPayments($payment);
        $order = $this->createOrderWithoutPurchaseUnits();
        $order->setPurchaseUnits([$purchaseUnit]);

        return $order;
    }

    /**
     * @return Order
     */
    private function createOrderWithoutPayments()
    {
        $purchaseUnit = new Order\PurchaseUnit();
        $order = $this->createOrderWithoutPurchaseUnits();
        $order->setPurchaseUnits([$purchaseUnit]);

        return $order;
    }

    /**
     * @return Order
     */
    private function createOrderWithoutPurchaseUnits()
    {
        $order = new Order();
        $order->setId('123456789');

        return $order;
    }

    /**
     * @return Order
     */
    private function createDefaultOrder()
    {
        $capture = new Order\PurchaseUnit\Payments\Capture();
        $capture->setId('123456789');
        $payment = new Order\PurchaseUnit\Payments();
        $payment->setCaptures([$capture]);
        $purchaseUnit = new Order\PurchaseUnit();
        $purchaseUnit->setPayments($payment);
        $order = new Order();
        $order->setPurchaseUnits([$purchaseUnit]);

        return $order;
    }
}
