<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Backend;

require_once __DIR__ . '/../../../../Controllers/Backend/PaypalUnifiedV2.php';

use Enlight_Class;
use Enlight_Controller_Action;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Exception;
use Generator;
use PDO;
use ReflectionClass;
use Shopware\Models\Order\Status;
use Shopware\Models\Shop\Shop;
use Shopware_Controllers_Backend_PaypalUnifiedV2;
use SwagPaymentPayPalUnified\Components\Exception\PayPalApiException;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\LoggerService;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Refund;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\AuthorizationResource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\CaptureResource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;
use UnexpectedValueException;

class PaypalUnifiedV2Test extends PaypalPaymentControllerTestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;

    const DEFAULT_PAYMENT_STATE = 99;

    /**
     * @dataProvider captureOrderTestDataProvider
     *
     * @param array<string,mixed> $requestParams
     * @param bool                $authorizationResourceExpectException
     * @param int                 $expectedPaymentStatus
     *
     * @return void
     */
    public function testCaptureOrder(array $requestParams, $authorizationResourceExpectException, $expectedPaymentStatus)
    {
        $this->insatllOrderForToTestUpdatePaymentStatus();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($requestParams);

        $authorizationResource = $this->createMock(AuthorizationResource::class);
        $exceptionHandler = $this->createMock(ExceptionHandlerService::class);

        if ($authorizationResourceExpectException) {
            $authorizationResource->method('capture')->willThrowException(new Exception('test exception'));
            $exceptionHandler->method('handle')->willReturn(new PayPalApiException(1, 'test exception'));
        }

        $controller = $this->getController(
            Shopware_Controllers_Backend_PaypalUnifiedV2::class,
            [
                self::SERVICE_AUTHORIZATION_RESOURCE => $authorizationResource,
                self::SERVICE_EXCEPTION_HANDLER_SERVICE => $exceptionHandler,
                self::SERVICE_PAYMENT_STATUS_SERVICE => $this->getContainer()->get('paypal_unified.payment_status_service'),
            ],
            $request
        );

        $controller->captureOrderAction();

        $this->evaluateTestResults($controller, $authorizationResourceExpectException, $requestParams['shopwareOrderId'], $expectedPaymentStatus);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function captureOrderTestDataProvider()
    {
        yield 'Expect success true and PAYMENT_STATE_COMPLETELY_PAID => 12 because its finalized' => [
            ['amount' => '5.00', 'maxCaptureAmount' => '5.00', 'finalize' => true, 'shopwareOrderId' => '173000', 'authorizationId' => '1', 'currency' => 'EUR', 'shopId' => 1],
            false,
            Status::PAYMENT_STATE_COMPLETELY_PAID,
        ];

        yield 'Expect success true and PAYMENT_STATE_PARTIALLY_PAID => 11' => [
            ['amount' => '2.00', 'maxCaptureAmount' => '5.00', 'finalize' => false, 'shopwareOrderId' => '173000', 'authorizationId' => '1', 'currency' => 'EUR', 'shopId' => 1],
            false,
            Status::PAYMENT_STATE_PARTIALLY_PAID,
        ];

        yield 'Expect success true and PAYMENT_STATE_COMPLETELY_PAID => 12' => [
            ['amount' => '5.00', 'maxCaptureAmount' => '5.00', 'finalize' => false, 'shopwareOrderId' => '173000', 'authorizationId' => '1', 'currency' => 'EUR', 'shopId' => 1],
            false,
            Status::PAYMENT_STATE_COMPLETELY_PAID,
        ];

        yield 'Expect success false because of a error while capturing' => [
            ['amount' => '5.00', 'maxCaptureAmount' => '5.00', 'finalize' => false, 'shopwareOrderId' => '173000', 'authorizationId' => '1', 'currency' => 'EUR', 'shopId' => 1],
            true,
            self::DEFAULT_PAYMENT_STATE,
        ];
    }

    /**
     * @dataProvider refundOrderTestDataProvider
     *
     * @param array<string,mixed> $requestParams
     * @param bool                $expectException
     * @param string              $paymentStatus
     * @param int                 $expectedPaymentStatus
     *
     * @return void
     */
    public function testRefundOrder(array $requestParams, $expectException, $paymentStatus, $expectedPaymentStatus)
    {
        $this->insatllOrderForToTestUpdatePaymentStatus();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($requestParams);

        $captureResource = $this->createMock(CaptureResource::class);
        $exceptionHandler = $this->createMock(ExceptionHandlerService::class);

        if ($expectException) {
            $captureResource->method('refund')->willThrowException(new Exception('test exception'));
            $exceptionHandler->method('handle')->willReturn(new PayPalApiException(1, 'test exception'));
        } else {
            $refund = new Refund();
            $refund->setStatus($paymentStatus);
            $captureResource->method('refund')->willReturn($refund);
        }

        $controller = $this->getController(
            Shopware_Controllers_Backend_PaypalUnifiedV2::class,
            [
                self::SERVICE_CAPTURE_RESOURCE => $captureResource,
                self::SERVICE_EXCEPTION_HANDLER_SERVICE => $exceptionHandler,
                self::SERVICE_PAYMENT_STATUS_SERVICE => $this->getContainer()->get('paypal_unified.payment_status_service'),
            ],
            $request
        );

        $controller->refundOrderAction();

        $this->evaluateTestResults($controller, $expectException, $requestParams['shopwareOrderId'], $expectedPaymentStatus);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function refundOrderTestDataProvider()
    {
        yield 'Expect success false because of a error while refund' => [
            ['amount' => '5.00', 'shopwareOrderId' => '173000', 'currency' => 'EUR', 'shopId' => 1],
            true,
            PaymentStatusV2::ORDER_REFUND_COMPLETED,
            self::DEFAULT_PAYMENT_STATE,
        ];

        yield 'Expect success true and DEFAULT_PAYMENT_STATE => 99 because ORDER_REFUND_PENDING' => [
            ['amount' => '5.00', 'shopwareOrderId' => '173000', 'currency' => 'EUR', 'shopId' => 1],
            false,
            PaymentStatusV2::ORDER_REFUND_PENDING,
            self::DEFAULT_PAYMENT_STATE,
        ];

        yield 'Expect success true and PAYMENT_STATE_RE_CREDITING => 20 because ORDER_REFUND_COMPLETED' => [
            ['amount' => '5.00', 'shopwareOrderId' => '173000', 'currency' => 'EUR', 'shopId' => 1],
            false,
            PaymentStatusV2::ORDER_REFUND_COMPLETED,
            Status::PAYMENT_STATE_RE_CREDITING,
        ];
    }

    /**
     * @dataProvider cancelAuthorizationTestDataProvider
     *
     * @param array<string,mixed> $requestParams
     * @param bool                $expectException
     * @param string              $authorizationStatus
     * @param int                 $expectedPaymentStatus
     *
     * @return void
     */
    public function testCancelAuthorization(array $requestParams, $expectException, $authorizationStatus, $expectedPaymentStatus)
    {
        $this->insatllOrderForToTestUpdatePaymentStatus();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($requestParams);

        $authorizationResource = $this->createMock(AuthorizationResource::class);
        $exceptionHandler = $this->createMock(ExceptionHandlerService::class);
        $orderResource = $this->createMock(OrderResource::class);

        if ($expectException) {
            $authorizationResource->method('void')->willThrowException(new Exception('test exception'));
            $exceptionHandler->method('handle')->willReturn(new PayPalApiException(1, 'test exception'));
        } else {
            $orderResource->method('get')->willReturn($this->getOrder($authorizationStatus));
        }

        $controller = $this->getController(
            Shopware_Controllers_Backend_PaypalUnifiedV2::class,
            [
                self::SERVICE_AUTHORIZATION_RESOURCE => $authorizationResource,
                self::SERVICE_ORDER_RESOURCE => $orderResource,
                self::SERVICE_EXCEPTION_HANDLER_SERVICE => $exceptionHandler,
                self::SERVICE_PAYMENT_STATUS_SERVICE => $this->getContainer()->get('paypal_unified.payment_status_service'),
                self::SERVICE_ORDER_PROPERTY_HELPER => $this->getContainer()->get('paypal_unified.order_property_helper'),
            ],
            $request
        );

        $controller->cancelAuthorizationAction();

        $this->evaluateTestResults($controller, $expectException, $requestParams['shopwareOrderId'], $expectedPaymentStatus);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function cancelAuthorizationTestDataProvider()
    {
        yield 'Expect success false because of a error while void' => [
            ['authorizationId' => '123456', 'token' => '123456', 'shopwareOrderId' => '173000', 'shopId' => 1],
            true,
            PaymentStatusV2::ORDER_AUTHORIZATION_PENDING,
            self::DEFAULT_PAYMENT_STATE,
        ];

        yield 'Expect success true and DEFAULT_PAYMENT_STATE => 99' => [
            ['authorizationId' => '123456', 'token' => '123456', 'shopwareOrderId' => '173000', 'shopId' => 1],
            false,
            PaymentStatusV2::ORDER_AUTHORIZATION_PENDING,
            self::DEFAULT_PAYMENT_STATE,
        ];

        yield 'Expect success true and PAYMENT_STATE_THE_PROCESS_HAS_BEEN_CANCELLED => 35' => [
            ['authorizationId' => '123456', 'token' => '123456', 'shopwareOrderId' => '173000', 'shopId' => 1],
            false,
            PaymentStatusV2::ORDER_VOIDED,
            Status::PAYMENT_STATE_THE_PROCESS_HAS_BEEN_CANCELLED,
        ];
    }

    /**
     * @return void
     */
    public function testPreDispatchShouldThrowException()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $controller = Enlight_Class::Instance(Shopware_Controllers_Backend_PaypalUnifiedV2::class, [$request, $response]);
        static::assertInstanceOf(Shopware_Controllers_Backend_PaypalUnifiedV2::class, $controller);

        if (method_exists($controller, 'setRequest')) {
            $controller->setRequest($request);
        }

        $controller->setRequest($request);
        $controller->setContainer($this->getContainer());

        static::expectException(UnexpectedValueException::class);

        $controller->preDispatch();
    }

    /**
     * @return void
     */
    public function testPreDispatchShouldUpdateShopId()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('shopId', 2);

        $response = new Enlight_Controller_Response_ResponseTestCase();

        $controller = Enlight_Class::Instance(Shopware_Controllers_Backend_PaypalUnifiedV2::class, [$request, $response]);

        static::assertInstanceOf(Shopware_Controllers_Backend_PaypalUnifiedV2::class, $controller);

        if (method_exists($controller, 'setRequest')) {
            $controller->setRequest($request);
        }

        $controller->setContainer($this->getContainer());

        $controller->preDispatch();

        $shop = $this->getContainer()->get('paypal_unified.dependency_provider')->getShop();
        static::assertInstanceOf(Shop::class, $shop);
        static::assertSame(2, $shop->getId());

        $this->getContainer()->get('paypal_unified.backend.shop_registration_service')->registerShopById(1);
    }

    /**
     * @return void
     */
    public function testRefundOrderActionShouldThrowException()
    {
        $controller = $this->createController(true);

        $controller->refundOrderAction();

        $result = $controller->View()->getAssign();

        static::assertFalse($result['success']);
        static::assertSame(999, $result['code']);
        static::assertSame('An error occurred', $result['message']);
    }

    /**
     * @return void
     */
    public function testRefundOrderAction()
    {
        $controller = $this->createController(false);

        $controller->refundOrderAction();

        $result = $controller->View()->getAssign();

        static::assertTrue($result['success']);
    }

    /**
     * @param bool $captureResourceWillThrowException
     *
     * @return Shopware_Controllers_Backend_PaypalUnifiedV2
     */
    private function createController($captureResourceWillThrowException)
    {
        $logger = $this->createMock(LoggerService::class);
        $exceptionHandler = $this->createMock(ExceptionHandlerService::class);
        $paymentStatusService = $this->createMock(PaymentStatusService::class);

        $refund = new Refund();
        $refund->setStatus(PaymentStatusV2::ORDER_CAPTURE_COMPLETED);

        $captureResource = $this->createMock(CaptureResource::class);
        if ($captureResourceWillThrowException) {
            $exception = $this->createException();
            $captureResource->method('refund')->willThrowException($exception);
            $exceptionHandler->expects(static::once())->method('handle')->willReturn($exception);
        } else {
            $captureResource->expects(static::once())->method('refund')->willReturn($refund);
            $paymentStatusService->expects(static::once())->method('updatePaymentStatusV2');
        }

        $request = $this->createRequest();

        $controller = Enlight_Class::Instance(Shopware_Controllers_Backend_PaypalUnifiedV2::class, [$request, new Enlight_Controller_Response_ResponseTestCase()]);
        if (method_exists($controller, 'setView')) {
            $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));
        }

        if (method_exists($controller, 'setRequest')) {
            $controller->setRequest($request);
        }

        static::assertInstanceOf(Shopware_Controllers_Backend_PaypalUnifiedV2::class, $controller);

        $this->addRequirementsToController($controller, $logger, $captureResource, $exceptionHandler, $paymentStatusService);

        return $controller;
    }

    /**
     * @return void
     */
    private function addRequirementsToController(
        Shopware_Controllers_Backend_PaypalUnifiedV2 $controller,
        LoggerService $logger,
        CaptureResource $captureResource,
        ExceptionHandlerService $exceptionHandler,
        PaymentStatusService $paymentStatusService
    ) {
        $reflectionClass = new ReflectionClass(Shopware_Controllers_Backend_PaypalUnifiedV2::class);

        $loggerReflectionProperty = $reflectionClass->getProperty('logger');
        $loggerReflectionProperty->setAccessible(true);
        $loggerReflectionProperty->setValue($controller, $logger);

        $captureResourceReflectionProperty = $reflectionClass->getProperty('captureResource');
        $captureResourceReflectionProperty->setAccessible(true);
        $captureResourceReflectionProperty->setValue($controller, $captureResource);

        $exceptionHandlerReflectionProperty = $reflectionClass->getProperty('exceptionHandler');
        $exceptionHandlerReflectionProperty->setAccessible(true);
        $exceptionHandlerReflectionProperty->setValue($controller, $exceptionHandler);

        $paymentStatusServiceReflectionProperty = $reflectionClass->getProperty('paymentStatusService');
        $paymentStatusServiceReflectionProperty->setAccessible(true);
        $paymentStatusServiceReflectionProperty->setValue($controller, $paymentStatusService);
    }

    /**
     * @return Enlight_Controller_Request_RequestTestCase
     */
    private function createRequest()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('shopwareOrderId', 1);
        $request->setParam('captureId', 1);
        $request->setParam('amount', 10.00);
        $request->setParam('currency', 'EUR');
        $request->setParam('note', 'This is a note');

        return $request;
    }

    /**
     * @return Exception
     */
    private function createException()
    {
        return new Exception('An error occurred', 999);
    }

    /**
     * @param string $authorizationStatus
     *
     * @return Order
     */
    private function getOrder($authorizationStatus)
    {
        $order = new Order();
        $order->setId('123456');
        $order->setPurchaseUnits([]);

        $purchaseUnit = new PurchaseUnit();
        $order->setPurchaseUnits([$purchaseUnit]);
        $payments = new Payments();

        $authorization = new Authorization();
        $authorization->setStatus($authorizationStatus);

        $purchaseUnit->setPayments($payments);
        $payments->setAuthorizations([$authorization]);

        return $order;
    }

    /**
     * @return void
     */
    private function insatllOrderForToTestUpdatePaymentStatus()
    {
        $orderSql = file_get_contents(__DIR__ . '/_fixtures/order_for_update_payment_status.sql');
        static::assertTrue(\is_string($orderSql));
        $this->getContainer()->get('dbal_connection')->exec($orderSql);
    }

    /**
     * @param int $shopwareOrderId
     *
     * @return int
     */
    private function getChangedPaymentStatus($shopwareOrderId)
    {
        return (int) $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->select('cleared')
            ->from('s_order')
            ->where('id = :shopwareOrderId')
            ->setParameter('shopwareOrderId', $shopwareOrderId)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * @param Enlight_Controller_Action $controller
     * @param bool                      $authorizationResourceExpectException
     * @param int                       $shopwareOrderId
     * @param int                       $expectedPaymentStatus
     *
     * @return void
     */
    private function evaluateTestResults($controller, $authorizationResourceExpectException, $shopwareOrderId, $expectedPaymentStatus)
    {
        $viewAssignResult = $controller->View()->getAssign();

        if ($authorizationResourceExpectException) {
            static::assertFalse($viewAssignResult['success']);
        } else {
            static::assertTrue($viewAssignResult['success']);
        }

        $result = $this->getChangedPaymentStatus($shopwareOrderId);

        static::assertSame($expectedPaymentStatus, $result);
    }
}
