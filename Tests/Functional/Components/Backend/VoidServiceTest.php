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
use SwagPaymentPayPalUnified\Components\Backend\VoidService;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\AuthorizationResourceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\LoggerMock;
use SwagPaymentPayPalUnified\Tests\Mocks\OrderResourceMock;

class VoidServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use OrderTrait;
    use ContainerTrait;

    const CURRENCY = CaptureServiceTest::CURRENCY;

    /**
     * @before
     */
    public function before()
    {
        $this->modelManager = $this->getContainer()->get('models');
        $this->connection = $this->getContainer()->get('dbal_connection');
    }

    public function testVoidOrder()
    {
        $orderId = $this->createOrder(OrderResourceMock::PAYPAL_PAYMENT_ID);

        $result = $this->createVoidService()->voidOrder('');

        $order = $this->getOrderById($orderId);

        static::assertSame(Status::PAYMENT_STATE_THE_PROCESS_HAS_BEEN_CANCELLED, $order->getPaymentStatus()->getId());
        static::assertTrue($result['success']);
    }

    public function testVoidOrderThrowException()
    {
        $orderId = $this->createOrder(OrderResourceMock::PAYPAL_PAYMENT_ID);

        $result = $this->createVoidService()->voidOrder(OrderResourceMock::THROW_EXCEPTION);

        $order = $this->getOrderById($orderId);

        static::assertSame(Status::PAYMENT_STATE_OPEN, $order->getPaymentStatus()->getId());
        static::assertFalse($result['success']);
    }

    public function testVoidAuthorization()
    {
        $orderId = $this->createOrder(AuthorizationResourceMock::PAYPAL_PAYMENT_ID);

        $result = $this->createVoidService()->voidAuthorization('');

        $order = $this->getOrderById($orderId);

        static::assertSame(Status::PAYMENT_STATE_THE_PROCESS_HAS_BEEN_CANCELLED, $order->getPaymentStatus()->getId());
        static::assertTrue($result['success']);
    }

    public function testVoidAuthorizationThrowException()
    {
        $orderId = $this->createOrder(AuthorizationResourceMock::PAYPAL_PAYMENT_ID);

        $result = $this->createVoidService()->voidAuthorization(AuthorizationResourceMock::THROW_EXCEPTION);

        $order = $this->getOrderById($orderId);

        static::assertSame(Status::PAYMENT_STATE_OPEN, $order->getPaymentStatus()->getId());
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
            new PaymentStatusService(
                $this->modelManager,
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
