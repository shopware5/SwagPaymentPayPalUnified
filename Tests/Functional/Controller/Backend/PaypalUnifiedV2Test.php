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
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopware_Controllers_Backend_PaypalUnifiedV2;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\LoggerService;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Refund;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\CaptureResource;

class PaypalUnifiedV2Test extends TestCase
{
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
}
