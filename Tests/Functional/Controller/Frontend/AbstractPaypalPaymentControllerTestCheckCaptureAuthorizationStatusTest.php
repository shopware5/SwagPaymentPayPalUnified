<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Generator;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\Services\OrderPropertyHelper;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactoryInterface;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\_mocks\AbstractPaypalPaymentControllerMock;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPaypalPaymentControllerTestCheckCaptureAuthorizationStatusTest extends PaypalPaymentControllerTestCase
{
    use ReflectionHelperTrait;

    /**
     * @dataProvider checkCaptureAuthorizationStatusTestDataProvider
     *
     * @param PaymentIntentV2::* $intent
     * @param PaymentStatusV2::* $status
     * @param bool               $methodWillThrowStatusException
     * @param bool               $methodWillThrowValueException
     * @param int                $expectedErrorCode
     *
     * @return void
     */
    public function testCheckCaptureAuthorizationStatus($intent, $status, $methodWillThrowStatusException, $methodWillThrowValueException, $expectedErrorCode)
    {
        $paypalOrder = $this->createPayPalOrder($intent, $status, $methodWillThrowValueException);

        $paymentControllerHelper = $this->createMock(PaymentControllerHelper::class);
        if ($methodWillThrowStatusException || $methodWillThrowValueException) {
            $paymentControllerHelper->expects(static::once())->method('handleError');
        }

        $orderPropertyHelperOriginal = $this->getContainer()->get(self::SERVICE_ORDER_PROPERTY_HELPER);
        $orderPropertyHelperMock = $this->createMock(OrderPropertyHelper::class);
        if ($intent === PaymentIntentV2::CAPTURE) {
            $capture = $orderPropertyHelperOriginal->getFirstCapture($paypalOrder);
            $orderPropertyHelperMock->expects(static::once())->method('getFirstCapture')->willReturn($capture);
        }

        if ($intent === PaymentIntentV2::AUTHORIZE) {
            $authorization = $orderPropertyHelperOriginal->getAuthorization($paypalOrder);
            $orderPropertyHelperMock->expects(static::once())->method('getAuthorization')->willReturn($authorization);
        }

        $controller = $this->getController(
            AbstractPaypalPaymentControllerMock::class,
            [
                self::SERVICE_ORDER_PROPERTY_HELPER => $orderPropertyHelperMock,
                self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY => $this->createRedirectDataBuilderFactory($methodWillThrowStatusException, $methodWillThrowValueException, $expectedErrorCode),
                self::SERVICE_PAYMENT_CONTROLLER_HELPER => $paymentControllerHelper,
            ]
        );

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentControllerMock::class, 'checkCaptureAuthorizationStatus');

        $reflectionMethod->invoke($controller, $paypalOrder);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function checkCaptureAuthorizationStatusTestDataProvider()
    {
        yield 'Intent is CAPTURE and status is ORDER_CAPTURE_COMPLETED' => [
            PaymentIntentV2::CAPTURE,
            PaymentStatusV2::ORDER_CAPTURE_COMPLETED,
            false,
            false,
            0,
        ];

        yield 'Intent is CAPTURE and status is ORDER_CAPTURE_DECLINED and expect a exception' => [
            PaymentIntentV2::CAPTURE,
            PaymentStatusV2::ORDER_CAPTURE_DECLINED,
            true,
            false,
            ErrorCodes::CAPTURE_DECLINED,
        ];

        yield 'Intent is CAPTURE and expect UnexpectedValueException' => [
            PaymentIntentV2::CAPTURE,
            PaymentStatusV2::ORDER_CAPTURE_COMPLETED,
            false,
            true,
            0,
        ];

        yield 'Intent is CAPTURE and status is ORDER_CAPTURE_FAILED and expect a exception' => [
            PaymentIntentV2::CAPTURE,
            PaymentStatusV2::ORDER_CAPTURE_FAILED,
            true,
            false,
            ErrorCodes::CAPTURE_FAILED,
        ];

        yield 'Intent is AUTHORIZE and status is ORDER_AUTHORIZATION_CREATED' => [
            PaymentIntentV2::AUTHORIZE,
            PaymentStatusV2::ORDER_AUTHORIZATION_CREATED,
            false,
            false,
            0,
        ];

        yield 'Intent is AUTHORIZE and status is ORDER_AUTHORIZATION_PENDING and expect a exception' => [
            PaymentIntentV2::AUTHORIZE,
            PaymentStatusV2::ORDER_AUTHORIZATION_DENIED,
            true,
            false,
            ErrorCodes::AUTHORIZATION_DENIED,
        ];

        yield 'Intent is AUTHORIZE and expect UnexpectedValueException' => [
            PaymentIntentV2::AUTHORIZE,
            PaymentStatusV2::ORDER_AUTHORIZATION_CREATED,
            false,
            true,
            0,
        ];

        yield 'Intent is AUTHORIZE and status is ORDER_AUTHORIZATION_DENIED and expect a exception' => [
            PaymentIntentV2::AUTHORIZE,
            PaymentStatusV2::ORDER_AUTHORIZATION_DENIED,
            true,
            false,
            ErrorCodes::AUTHORIZATION_DENIED,
        ];
    }

    /**
     * @param PaymentIntentV2::* $intent
     * @param PaymentStatusV2::* $status
     * @param bool               $methodWillThrowValueException
     *
     * @return Order
     */
    private function createPayPalOrder($intent, $status, $methodWillThrowValueException)
    {
        $capture = new Capture();
        $capture->setStatus($status);

        $authorization = new Authorization();
        $authorization->setStatus($status);

        $payments = new Payments();

        if (!$methodWillThrowValueException) {
            $payments->setAuthorizations([$authorization]);
            $payments->setCaptures([$capture]);
        }

        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setPayments($payments);

        $order = new Order();
        $order->setIntent($intent);
        $order->setPurchaseUnits([$purchaseUnit]);

        return $order;
    }

    /**
     * @param bool $methodWillThrowStatusException
     * @param bool $methodWillThrowValueException
     * @param int  $expectedErrorCode
     *
     * @return RedirectDataBuilderFactoryInterface
     */
    private function createRedirectDataBuilderFactory($methodWillThrowStatusException, $methodWillThrowValueException, $expectedErrorCode)
    {
        $redirectDataBuilderFactoryMock = $this->createMock(RedirectDataBuilderFactoryInterface::class);
        $redirectDataBuilder = $this->createMock(RedirectDataBuilder::class);
        $redirectDataBuilder->method('setException')->willReturn($redirectDataBuilder);

        if ($methodWillThrowStatusException) {
            $redirectDataBuilder->expects(static::once())->method('setCode')->with($expectedErrorCode)->willReturn($redirectDataBuilder);
            $redirectDataBuilderFactoryMock->expects(static::once())->method('createRedirectDataBuilder')->willReturn($redirectDataBuilder);
        }

        if ($methodWillThrowValueException) {
            $redirectDataBuilder->expects(static::once())->method('setCode')->with(ErrorCodes::UNKNOWN)->willReturn($redirectDataBuilder);
            $redirectDataBuilderFactoryMock->expects(static::once())->method('createRedirectDataBuilder')->willReturn($redirectDataBuilder);
        }

        return $redirectDataBuilderFactoryMock;
    }
}
