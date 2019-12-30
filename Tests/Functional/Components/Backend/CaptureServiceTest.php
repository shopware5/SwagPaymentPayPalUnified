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
use SwagPaymentPayPalUnified\Components\Backend\CaptureService;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\AuthorizationResourceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\CaptureResourceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\LoggerMock;
use SwagPaymentPayPalUnified\Tests\Mocks\OrderResourceMock;

class CaptureServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use OrderTrait;

    const CURRENCY = 'EUR';

    /**
     * @before
     */
    public function before()
    {
        $this->modelManager = Shopware()->Container()->get('models');
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

        /** @var Order $order */
        $order = $this->modelManager->getRepository(Order::class)->find($orderId);
        static::assertSame(PaymentStatus::PAYMENT_STATUS_PAID, $order->getPaymentStatus()->getId());
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

        /** @var Order $order */
        $order = $this->modelManager->getRepository(Order::class)->find($orderId);
        static::assertSame(PaymentStatus::PAYMENT_STATUS_PARTIALLY_PAID, $order->getPaymentStatus()->getId());
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

        /** @var Order $order */
        $order = $this->modelManager->getRepository(Order::class)->find($orderId);
        static::assertSame(PaymentStatus::PAYMENT_STATUS_OPEN, $order->getPaymentStatus()->getId());
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

        /** @var Order $order */
        $order = $this->modelManager->getRepository(Order::class)->find($orderId);
        static::assertSame(PaymentStatus::PAYMENT_STATUS_PAID, $order->getPaymentStatus()->getId());
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

        /** @var Order $order */
        $order = $this->modelManager->getRepository(Order::class)->find($orderId);
        static::assertSame(PaymentStatus::PAYMENT_STATUS_OPEN, $order->getPaymentStatus()->getId());
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

        /** @var Order $order */
        $order = $this->modelManager->getRepository(Order::class)->find($orderId);
        static::assertSame(PaymentStatus::PAYMENT_STATUS_REFUNDED, $order->getPaymentStatus()->getId());
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

        /** @var Order $order */
        $order = $this->modelManager->getRepository(Order::class)->find($orderId);
        static::assertSame(PaymentStatus::PAYMENT_STATUS_OPEN, $order->getPaymentStatus()->getId());
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
            new PaymentStatusService(Shopware()->Container()->get('models'))
        );
    }
}
