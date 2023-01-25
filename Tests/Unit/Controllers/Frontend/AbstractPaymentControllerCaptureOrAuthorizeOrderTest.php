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
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InstrumentDeclinedException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\NoOrderToProceedException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\PayerActionRequiredException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\RequireRestartException;
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

        $resultOrder = $reflectionMethod->invoke($abstractController, $payPalOrder);

        static::assertInstanceOf(Order::class, $resultOrder);
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

        $resultOrder = $reflectionMethod->invoke($abstractController, $payPalOrder);

        static::assertInstanceOf(Order::class, $resultOrder);
        static::assertSame($status, $resultOrder->getStatus());
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
     * @param string $expectedException
     *
     * @return void
     */
    public function testCaptureOrAuthorizeOrderShouldThrowException($method, $intent, Exception $exception, $expectedException)
    {
        $payPalOrder = $this->createPayPalOrder($intent, PaymentStatusV2::ORDER_CREATED);

        $orderResource = $this->createMock(OrderResource::class);
        $orderResource->expects(static::once())->method($method)->willThrowException($exception);

        $abstractController = $this->getController(AbstractPaypalPaymentController::class, [
            self::SERVICE_ORDER_RESOURCE => $orderResource,
        ]);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, 'captureOrAuthorizeOrder');

        static::expectException($expectedException);

        $result = $reflectionMethod->invoke($abstractController, $payPalOrder);

        static::assertNull($result);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function captureOrAuthorizeOrderShouldThrowExceptionTestDataProvider()
    {
        yield 'CAPTURE expects Exception RequireRestartException' => [
            self::RESOURCE_METHOD_CAPTURE,
            PaymentIntentV2::CAPTURE,
            new RequestException('Error', 0, null, (string) json_encode(['details' => [['issue' => 'DUPLICATE_INVOICE_ID']]])),
            RequireRestartException::class,
        ];

        yield 'CAPTURE expects Exception PayerActionRequiredException' => [
            self::RESOURCE_METHOD_CAPTURE,
            PaymentIntentV2::CAPTURE,
            new RequestException('Error', 0, null, (string) json_encode(['details' => [['issue' => 'PAYER_ACTION_REQUIRED']]])),
            PayerActionRequiredException::class,
        ];

        yield 'CAPTURE expects Exception InstrumentDeclinedException' => [
            self::RESOURCE_METHOD_CAPTURE,
            PaymentIntentV2::CAPTURE,
            new RequestException('Error', 0, null, (string) json_encode(['details' => [['issue' => 'INSTRUMENT_DECLINED']]])),
            InstrumentDeclinedException::class,
        ];

        yield 'CAPTURE expects Exception NoOrderToProceedException' => [
            self::RESOURCE_METHOD_CAPTURE,
            PaymentIntentV2::CAPTURE,
            new Exception('Error'),
            NoOrderToProceedException::class,
        ];

        yield 'AUTHORIZE expects Exception RequireRestartException' => [
            self::RESOURCE_METHOD_AUTHORIZE,
            PaymentIntentV2::AUTHORIZE,
            new RequestException('Error', 0, null, (string) json_encode(['details' => [['issue' => 'DUPLICATE_INVOICE_ID']]])),
            RequireRestartException::class,
        ];

        yield 'AUTHORIZE expects Exception PayerActionRequiredException' => [
            self::RESOURCE_METHOD_AUTHORIZE,
            PaymentIntentV2::AUTHORIZE,
            new RequestException('Error', 0, null, (string) json_encode(['details' => [['issue' => 'PAYER_ACTION_REQUIRED']]])),
            PayerActionRequiredException::class,
        ];

        yield 'AUTHORIZE expects Exception InstrumentDeclinedException' => [
            self::RESOURCE_METHOD_AUTHORIZE,
            PaymentIntentV2::AUTHORIZE,
            new RequestException('Error', 0, null, (string) json_encode(['details' => [['issue' => 'INSTRUMENT_DECLINED']]])),
            InstrumentDeclinedException::class,
        ];

        yield 'AUTHORIZE expects Exception NoOrderToProceedException' => [
            self::RESOURCE_METHOD_AUTHORIZE,
            PaymentIntentV2::AUTHORIZE,
            new Exception('Error'),
            NoOrderToProceedException::class,
        ];
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
