<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Template_Manager;
use Generator;
use ReflectionClass;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\OrderResource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Giropay;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payee;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\_mocks\AbstractPaypalPaymentControllerMock;
use SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\_mocks\ViewMock;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPaypalPaymentControllerTestIsPaymentCompletedTest extends PaypalPaymentControllerTestCase
{
    const EXPECTED_RESULT_FALSE = false;
    const EXPECTED_RESULT_TRUE = true;
    const NOT_RELEVANT_FOR_THIS_TEST_CASE = 'NOT_RELEVANT_FOR_THIS_TEST_CASE';

    /**
     * @dataProvider isPaymentCompletedTestDataProvider
     *
     * @param PaymentIntentV2::* $intent
     * @param string             $authorizationStatus
     * @param string             $captureStatus
     * @param bool               $expectedResult
     * @param int|null           $expectedErrorCode
     * @param bool               $orderHasAuthorizationResult
     * @param bool               $orderHasCaptureResult
     *
     * @return void
     */
    public function testIsPaymentCompleted(
        $intent,
        $authorizationStatus,
        $captureStatus,
        $expectedResult,
        $expectedErrorCode = null,
        $orderHasAuthorizationResult = true,
        $orderHasCaptureResult = true
    ) {
        $orderResource = $this->createMock(OrderResource::class);
        $orderResource->method('get')->willReturn(
            $this->createPayPalOrder($intent, $authorizationStatus, $captureStatus, $orderHasAuthorizationResult, $orderHasCaptureResult)
        );

        $orderPropertyHelper = $this->getContainer()->get('paypal_unified.order_property_helper');

        $redirectDataBuilder = $this->getRedirectDataBuilder();
        $redirectDataBuilder->method('getCode')->willReturn(999);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setHeader('X-Requested-With', 'XMLHttpRequest');
        $request->setParam('shopId', 1);

        $view = new ViewMock(new Enlight_Template_Manager());

        $controller = $this->getController(
            AbstractPaypalPaymentControllerMock::class,
            [
                self::SERVICE_ORDER_RESOURCE => $orderResource,
                self::SERVICE_ORDER_PROPERTY_HELPER => $orderPropertyHelper,
                self::SERVICE_PAYMENT_CONTROLLER_HELPER => $this->getContainer()->get(self::SERVICE_PAYMENT_CONTROLLER_HELPER),
                self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY => $this->getContainer()->get(self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY),
            ],
            $request,
            null,
            $view
        );

        $this->prepareRedirectDataBuilderFactory();

        $reflectionMethod = (new ReflectionClass(AbstractPaypalPaymentControllerMock::class))->getMethod('isPaymentCompleted');
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invoke($controller, 'anyPayPalOrderId');

        if ($expectedErrorCode !== null) {
            static::assertSame($expectedErrorCode, $controller->View()->getAssign()['paypalUnifiedErrorCode']);
        }

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function isPaymentCompletedTestDataProvider()
    {
        yield 'Intent is PaymentIntentV2::AUTHORIZE authorization status ORDER_AUTHORIZATION_DENIED is in $paymentFailed array' => [
            PaymentIntentV2::AUTHORIZE,
            PaymentStatusV2::ORDER_AUTHORIZATION_DENIED,
            self::NOT_RELEVANT_FOR_THIS_TEST_CASE,
            self::EXPECTED_RESULT_FALSE,
            ErrorCodes::UNKNOWN,
        ];

        yield 'Intent is PaymentIntentV2::AUTHORIZE authorization status ORDER_AUTHORIZATION_PARTIALLY_CREATED is in $paymentFailed array' => [
            PaymentIntentV2::AUTHORIZE,
            PaymentStatusV2::ORDER_AUTHORIZATION_PARTIALLY_CREATED,
            self::NOT_RELEVANT_FOR_THIS_TEST_CASE,
            self::EXPECTED_RESULT_FALSE,
            ErrorCodes::UNKNOWN,
        ];

        yield 'Intent is PaymentIntentV2::AUTHORIZE authorization status ORDER_AUTHORIZATION_VOIDED is in $paymentFailed array' => [
            PaymentIntentV2::AUTHORIZE,
            PaymentStatusV2::ORDER_AUTHORIZATION_VOIDED,
            self::NOT_RELEVANT_FOR_THIS_TEST_CASE,
            self::EXPECTED_RESULT_FALSE,
            ErrorCodes::UNKNOWN,
        ];

        yield 'Intent is PaymentIntentV2::AUTHORIZE authorization status ORDER_AUTHORIZATION_EXPIRED is in $paymentFailed array' => [
            PaymentIntentV2::AUTHORIZE,
            PaymentStatusV2::ORDER_AUTHORIZATION_EXPIRED,
            self::NOT_RELEVANT_FOR_THIS_TEST_CASE,
            self::EXPECTED_RESULT_FALSE,
            ErrorCodes::UNKNOWN,
        ];

        yield 'Intent is PaymentIntentV2::AUTHORIZE authorization status ORDER_AUTHORIZATION_CREATED' => [
            PaymentIntentV2::AUTHORIZE,
            PaymentStatusV2::ORDER_AUTHORIZATION_CREATED,
            self::NOT_RELEVANT_FOR_THIS_TEST_CASE,
            self::EXPECTED_RESULT_TRUE,
        ];

        yield 'Intent is PaymentIntentV2::AUTHORIZE and authorization is not set to payment object' => [
            PaymentIntentV2::AUTHORIZE,
            self::NOT_RELEVANT_FOR_THIS_TEST_CASE,
            self::NOT_RELEVANT_FOR_THIS_TEST_CASE,
            self::EXPECTED_RESULT_FALSE,
            ErrorCodes::UNKNOWN,
            false,
        ];

        yield 'Intent is PaymentIntentV2::CAPTURE capture status ORDER_CAPTURE_DECLINED is in $paymentFailed array' => [
            PaymentIntentV2::CAPTURE,
            self::NOT_RELEVANT_FOR_THIS_TEST_CASE,
            PaymentStatusV2::ORDER_CAPTURE_DECLINED,
            self::EXPECTED_RESULT_FALSE,
            ErrorCodes::UNKNOWN,
        ];

        yield 'Intent is PaymentIntentV2::CAPTURE capture status ORDER_CAPTURE_FAILED is in $paymentFailed array' => [
            PaymentIntentV2::CAPTURE,
            self::NOT_RELEVANT_FOR_THIS_TEST_CASE,
            PaymentStatusV2::ORDER_CAPTURE_FAILED,
            self::EXPECTED_RESULT_FALSE,
            ErrorCodes::UNKNOWN,
        ];

        yield 'Intent is PaymentIntentV2::CAPTURE capture status ORDER_CAPTURE_COMPLETED' => [
            PaymentIntentV2::CAPTURE,
            self::NOT_RELEVANT_FOR_THIS_TEST_CASE,
            PaymentStatusV2::ORDER_CAPTURE_COMPLETED,
            self::EXPECTED_RESULT_TRUE,
        ];

        yield 'Intent is PaymentIntentV2::CAPTURE and capture is not set to payment object' => [
            PaymentIntentV2::CAPTURE,
            self::NOT_RELEVANT_FOR_THIS_TEST_CASE,
            self::NOT_RELEVANT_FOR_THIS_TEST_CASE,
            self::EXPECTED_RESULT_FALSE,
            ErrorCodes::UNKNOWN,
            true,
            false,
        ];
    }

    /**
     * @param PaymentIntentV2::* $intent
     * @param string             $authorizationStatus
     * @param string             $captureStatus
     * @param bool               $orderHasAuthorizationResult
     * @param bool               $orderHasCaptureResult
     *
     * @return Order
     */
    private function createPayPalOrder(
        $intent,
        $authorizationStatus,
        $captureStatus,
        $orderHasAuthorizationResult,
        $orderHasCaptureResult
    ) {
        $amount = new Amount();
        $amount->setValue('347.89');
        $amount->setCurrencyCode('EUR');

        $payee = new Payee();
        $payee->setEmailAddress('test@business.example.com');

        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setAmount($amount);
        $purchaseUnit->setPayee($payee);

        $payments = new Payments();

        if ($orderHasAuthorizationResult) {
            $authorization = new Authorization();
            $authorization->setStatus($authorizationStatus);
            $payments->setAuthorizations([$authorization]);
        }

        if ($orderHasCaptureResult) {
            $capture = new Capture();
            $capture->setStatus($captureStatus);
            $payments->setCaptures([$capture]);
        }

        $purchaseUnit->setPayments($payments);

        $giroPay = new Giropay();
        $giroPay->setCountryCode('DE');
        $giroPay->setName('Max Mustermann');

        $paymentSource = new Order\PaymentSource();
        $paymentSource->setGiropay($giroPay);

        $order = new Order();
        $order->setId('123456');
        $order->setIntent($intent);
        $order->setCreateTime('2022-04-25T06:51:36Z');
        $order->setStatus('APPROVED');
        $order->setPurchaseUnits([$purchaseUnit]);
        $order->setPaymentSource($paymentSource);

        return $order;
    }
}
