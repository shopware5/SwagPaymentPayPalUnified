<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Backend;

use PHPUnit\Framework\TestCase;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Backend\CaptureService;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\AuthorizationResourceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\CaptureResourceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\LoggerMock;
use SwagPaymentPayPalUnified\Tests\Mocks\OrderResourceMock;

class CaptureServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use OrderTrait;
    use ContainerTrait;

    const CURRENCY = 'EUR';

    /**
     * @before
     */
    public function before()
    {
        $this->modelManager = $this->getContainer()->get('models');
        $this->connection = $this->getContainer()->get('dbal_connection');
    }

    public function testCaptureAuthorization()
    {
        $orderId = $this->createOrder(AuthorizationResourceMock::PAYPAL_PAYMENT_ID);
        $result = $this->createCaptureService()->captureAuthorization(
            '',
            '21.34',
            self::CURRENCY,
            true
        );

        $order = $this->getOrderById($orderId);

        static::assertSame(Status::PAYMENT_STATE_COMPLETELY_PAID, $order->getPaymentStatus()->getId());
        static::assertNotNull($order->getClearedDate());
        static::assertTrue($result['success']);
    }

    public function testCaptureAuthorizationPartially()
    {
        $orderId = $this->createOrder(AuthorizationResourceMock::PAYPAL_PAYMENT_ID);
        $result = $this->createCaptureService()->captureAuthorization(
            '',
            '21.34',
            self::CURRENCY,
            false
        );

        $order = $this->getOrderById($orderId);

        static::assertSame(Status::PAYMENT_STATE_PARTIALLY_PAID, $order->getPaymentStatus()->getId());
        static::assertNotNull($order->getClearedDate());
        static::assertTrue($result['success']);
    }

    public function testCaptureAuthorizationThrowException()
    {
        $orderId = $this->createOrder(AuthorizationResourceMock::PAYPAL_PAYMENT_ID);
        $result = $this->createCaptureService()->captureAuthorization(
            AuthorizationResourceMock::THROW_EXCEPTION,
            '21.34',
            self::CURRENCY,
            true
        );

        $order = $this->getOrderById($orderId);

        static::assertSame(Status::PAYMENT_STATE_OPEN, $order->getPaymentStatus()->getId());
        static::assertFalse($result['success']);
    }

    public function testCaptureOrder()
    {
        $orderId = $this->createOrder(OrderResourceMock::PAYPAL_PAYMENT_ID);
        $result = $this->createCaptureService()->captureOrder(
            '',
            '21.34',
            self::CURRENCY,
            true
        );

        $order = $this->getOrderById($orderId);

        static::assertSame(Status::PAYMENT_STATE_COMPLETELY_PAID, $order->getPaymentStatus()->getId());
        static::assertNotNull($order->getClearedDate());
        static::assertTrue($result['success']);
    }

    public function testCaptureOrderThrowException()
    {
        $orderId = $this->createOrder(OrderResourceMock::PAYPAL_PAYMENT_ID);
        $result = $this->createCaptureService()->captureOrder(
            OrderResourceMock::THROW_EXCEPTION,
            '21.34',
            self::CURRENCY,
            true
        );

        $order = $this->getOrderById($orderId);

        static::assertSame(Status::PAYMENT_STATE_OPEN, $order->getPaymentStatus()->getId());
        static::assertFalse($result['success']);
    }

    public function testRefundCapture()
    {
        $orderId = $this->createOrder(CaptureResourceMock::PAYPAL_PAYMENT_ID);
        $result = $this->createCaptureService()->refundCapture(
            '',
            '21.34',
            self::CURRENCY,
            ''
        );

        $order = $this->getOrderById($orderId);

        static::assertSame(Status::PAYMENT_STATE_RE_CREDITING, $order->getPaymentStatus()->getId());
        static::assertTrue($result['success']);
    }

    public function testRefundCaptureThrowException()
    {
        $orderId = $this->createOrder(CaptureResourceMock::PAYPAL_PAYMENT_ID);
        $result = $this->createCaptureService()->refundCapture(
            CaptureResourceMock::THROW_EXCEPTION,
            '21.34',
            self::CURRENCY,
            ''
        );

        $order = $this->getOrderById($orderId);

        static::assertSame(Status::PAYMENT_STATE_OPEN, $order->getPaymentStatus()->getId());
        static::assertFalse($result['success']);
    }

    private function createCaptureService()
    {
        return new CaptureService(
            new ExceptionHandlerService(
                new LoggerMock()
            ),
            new OrderResourceMock(),
            new AuthorizationResourceMock(),
            new CaptureResourceMock(),
            new PaymentStatusService(
                $this->getContainer()->get('models'),
                $this->getContainer()->get('paypal_unified.logger_service'),
                $this->getContainer()->get('dbal_connection'),
                $this->getContainer()->get('paypal_unified.dependency_provider')
            )
        );
    }

    /**
     * @param int $orderId
     *
     * @return Order
     */
    private function getOrderById($orderId)
    {
        $this->modelManager->clear(Order::class);
        $order = $this->modelManager->getRepository(Order::class)->find($orderId);

        static::assertInstanceOf(Order::class, $order);

        return $order;
    }
}
