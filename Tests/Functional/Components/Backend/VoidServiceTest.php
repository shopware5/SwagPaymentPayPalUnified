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
use SwagPaymentPayPalUnified\Components\Backend\VoidService;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\AuthorizationResourceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\LoggerMock;
use SwagPaymentPayPalUnified\Tests\Mocks\OrderResourceMock;

class VoidServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use OrderTrait;

    const CURRENCY = CaptureServiceTest::CURRENCY;

    /**
     * @before
     */
    public function before()
    {
        $this->modelManager = Shopware()->Container()->get('models');
    }

    public function testVoidOrder()
    {
        $orderId = $this->createOrder(OrderResourceMock::PAYPAL_PAYMENT_ID);

        $result = $this->createVoidService()->voidOrder('');

        /** @var Order $order */
        $order = $this->modelManager->getRepository(Order::class)->find($orderId);
        static::assertSame(PaymentStatus::PAYMENT_STATUS_CANCELLED, $order->getPaymentStatus()->getId());
        static::assertTrue($result['success']);
    }

    public function testVoidOrderThrowException()
    {
        $orderId = $this->createOrder(OrderResourceMock::PAYPAL_PAYMENT_ID);

        $result = $this->createVoidService()->voidOrder(OrderResourceMock::THROW_EXCEPTION);

        /** @var Order $order */
        $order = $this->modelManager->getRepository(Order::class)->find($orderId);
        static::assertSame(PaymentStatus::PAYMENT_STATUS_OPEN, $order->getPaymentStatus()->getId());
        static::assertFalse($result['success']);
    }

    public function testVoidAuthorization()
    {
        $orderId = $this->createOrder(AuthorizationResourceMock::PAYPAL_PAYMENT_ID);

        $result = $this->createVoidService()->voidAuthorization('');

        /** @var Order $order */
        $order = $this->modelManager->getRepository(Order::class)->find($orderId);
        static::assertSame(PaymentStatus::PAYMENT_STATUS_CANCELLED, $order->getPaymentStatus()->getId());
        static::assertTrue($result['success']);
    }

    public function testVoidAuthorizationThrowException()
    {
        $orderId = $this->createOrder(AuthorizationResourceMock::PAYPAL_PAYMENT_ID);

        $result = $this->createVoidService()->voidAuthorization(AuthorizationResourceMock::THROW_EXCEPTION);

        /** @var Order $order */
        $order = $this->modelManager->getRepository(Order::class)->find($orderId);
        static::assertSame(PaymentStatus::PAYMENT_STATUS_OPEN, $order->getPaymentStatus()->getId());
        static::assertFalse($result['success']);
    }

    private function createVoidService()
    {
        return new VoidService(
            new ExceptionHandlerService(
                new LoggerMock()
            ),
            new AuthorizationResourceMock(),
            new OrderResourceMock(),
            new PaymentStatusService($this->modelManager)
        );
    }
}
