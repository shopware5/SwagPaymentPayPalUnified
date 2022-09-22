<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Controllers\Frontend;

use Exception;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentControllerResults\CaptureAuthorizeResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Giropay;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payee;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPaymentControllerCaptureOrAuthorizeOrderTest extends PaypalPaymentControllerTestCase
{
    use ReflectionHelperTrait;

    const RESOURCE_METHOD_CAPTURE = 'capture';
    const RESOURCE_METHOD_AUTHORIZE = 'authorize';

    /**
     * @return void
     */
    public function testCaptureOrAuthorizeOrderShouldReturnOrderBecauseStatusIsCompleted()
    {
        $payPalOrder = $this->createPayPalOrder(PaymentIntentV2::AUTHORIZE, PaymentStatusV2::ORDER_COMPLETED);

        $abstractController = $this->getController(AbstractPaypalPaymentController::class, []);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, 'captureOrAuthorizeOrder');

        /** @var CaptureAuthorizeResult $result */
        $result = $reflectionMethod->invoke($abstractController, $payPalOrder);

        static::assertFalse($result->getRequireRestart());
        static::assertInstanceOf(Order::class, $result->getOrder());
    }

    /**
     * @dataProvider captureOrAuthorizeOrderShouldReturnOrderSuccessfulTestDataProvider
     *
     * @param string $method
     * @param string $intent
     * @param string $status
     *
     * @return void
     */
    public function testCaptureOrAuthorizeOrderShouldReturnOrderSuccessful($method, $intent, $status)
    {
        $payPalOrder = $this->createPayPalOrder($intent, PaymentStatusV2::ORDER_CREATED);

        $orderResource = $this->createOrderResource($method, $payPalOrder, $status);

        $abstractController = $this->getController(AbstractPaypalPaymentController::class, [
            self::SERVICE_ORDER_RESOURCE => $orderResource,
        ]);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, 'captureOrAuthorizeOrder');

        /** @var CaptureAuthorizeResult $result */
        $result = $reflectionMethod->invoke($abstractController, $payPalOrder);

        static::assertFalse($result->getRequireRestart());
        static::assertInstanceOf(Order::class, $result->getOrder());
        static::assertSame($status, $result->getOrder()->getStatus());
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function captureOrAuthorizeOrderShouldReturnOrderSuccessfulTestDataProvider()
    {
        yield 'Intent is CAPTURE and expected status is ORDER_CAPTURE_COMPLETED' => [
            self::RESOURCE_METHOD_CAPTURE,
            PaymentIntentV2::CAPTURE,
            PaymentStatusV2::ORDER_CREATED,
        ];

        yield 'Intent is AUTHORIZE and expected status is ORDER_AUTHORIZATION_CREATED' => [
            self::RESOURCE_METHOD_AUTHORIZE,
            PaymentIntentV2::AUTHORIZE,
            PaymentStatusV2::ORDER_AUTHORIZATION_CREATED,
        ];
    }

    /**
     * @dataProvider captureOrAuthorizeOrderShouldThrowExceptionTestDataProvider
     *
     * @param string $method
     * @param string $intent
     * @param bool   $requireRestart
     *
     * @return void
     */
    public function testCaptureOrAuthorizeOrderShouldThrowException($method, $intent, Exception $exception, $requireRestart = false)
    {
        $payPalOrder = $this->createPayPalOrder($intent, PaymentStatusV2::ORDER_CREATED);

        $orderResource = $this->createMock(OrderResource::class);
        $orderResource->expects(static::once())->method($method)->willThrowException($exception);

        $abstractController = $this->getController(AbstractPaypalPaymentController::class, [
            self::SERVICE_ORDER_RESOURCE => $orderResource,
        ]);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, 'captureOrAuthorizeOrder');

        /** @var CaptureAuthorizeResult $result */
        $result = $reflectionMethod->invoke($abstractController, $payPalOrder);

        static::assertNull($result->getOrder());
        if ($requireRestart) {
            static::assertTrue($result->getRequireRestart());
        } else {
            static::assertFalse($result->getRequireRestart());
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function captureOrAuthorizeOrderShouldThrowExceptionTestDataProvider()
    {
        yield 'CAPTURE CaptureAuthorizeResult requires a restart' => [
            self::RESOURCE_METHOD_CAPTURE,
            PaymentIntentV2::CAPTURE,
            new RequestException('Error', 0, null, (string) json_encode(['details' => [['issue' => 'DUPLICATE_INVOICE_ID']]])),
            true,
        ];

        yield 'CAPTURE CaptureAuthorizeResult requires no restart' => [
            self::RESOURCE_METHOD_CAPTURE,
            PaymentIntentV2::CAPTURE,
            new RequestException('Error'),
        ];

        yield 'CAPTURE CaptureAuthorizeResult requires no restart with other exception type' => [
            self::RESOURCE_METHOD_CAPTURE,
            PaymentIntentV2::CAPTURE,
            new Exception('Error'),
        ];

        yield 'AUTHORIZE CaptureAuthorizeResult requires a restart' => [
            self::RESOURCE_METHOD_AUTHORIZE,
            PaymentIntentV2::AUTHORIZE,
            new RequestException('Error', 0, null, (string) json_encode(['details' => [['issue' => 'DUPLICATE_INVOICE_ID']]])),
            true,
        ];

        yield 'AUTHORIZE CaptureAuthorizeResult requires no restart' => [
            self::RESOURCE_METHOD_AUTHORIZE,
            PaymentIntentV2::AUTHORIZE,
            new RequestException('Error'),
        ];

        yield 'AUTHORIZE CaptureAuthorizeResult requires no restart with other exception type' => [
            self::RESOURCE_METHOD_AUTHORIZE,
            PaymentIntentV2::AUTHORIZE,
            new Exception('Error'),
        ];
    }

    /**
     * @return void
     */
    public function testCaptureOrAuthorizeOrderNoConditionMatches()
    {
        $payPalOrder = $this->createPayPalOrder('otherIntent', PaymentStatusV2::ORDER_CREATED);

        $abstractController = $this->getController(AbstractPaypalPaymentController::class, []);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, 'captureOrAuthorizeOrder');

        /** @var CaptureAuthorizeResult $result */
        $result = $reflectionMethod->invoke($abstractController, $payPalOrder);

        static::assertFalse($result->getRequireRestart());
        static::assertNull($result->getOrder());
    }

    /**
     * @param string $method
     * @param string $status
     *
     * @return OrderResource&MockObject
     */
    private function createOrderResource($method, Order $order, $status)
    {
        $order->setStatus($status);

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->expects(static::once())->method($method)->willReturn($order);

        return $orderResourceMock;
    }

    /**
     * @param string $intent
     * @param string $status
     *
     * @return Order
     */
    private function createPayPalOrder($intent, $status)
    {
        $amount = new Amount();
        $amount->setValue('347.89');
        $amount->setCurrencyCode('EUR');

        $payee = new Payee();
        $payee->setEmailAddress('test@business.example.com');

        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setAmount($amount);
        $purchaseUnit->setPayee($payee);

        $giroPay = new Giropay();
        $giroPay->setCountryCode('DE');
        $giroPay->setName('Max Mustermann');

        $paymentSource = new PaymentSource();
        $paymentSource->setGiropay($giroPay);

        $order = new Order();
        $order->setIntent($intent);
        $order->setCreateTime('2022-04-25T06:51:36Z');
        $order->setStatus($status);
        $order->setPurchaseUnits([$purchaseUnit]);
        $order->setPaymentSource($paymentSource);

        return $order;
    }
}
