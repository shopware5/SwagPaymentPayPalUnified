<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Exception\OrderNotFoundException;
use SwagPaymentPayPalUnified\Components\Services\LoggerService;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use UnexpectedValueException;

class PaymentStatusServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use ContainerTrait;

    const ANY_ID = 9999;
    const SHOPWARE_ORDER_ID = 66;
    const EXPECTS_EXCEPTION = true;
    const EXPECTS_NO_EXCEPTION = false;

    /**
     * @dataProvider UpdatePaymentStatusV2TestDataProvider
     *
     * @param int         $shopwareOrderId
     * @param int         $paymentStateId
     * @param bool        $expectsException
     * @param string|null $exceptionClassString
     *
     * @return void
     */
    public function testUpdatePaymentStatusV2($shopwareOrderId, $paymentStateId, $expectsException, $exceptionClassString = null)
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/order_payment_state_test.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->exec($sql);

        $paymentStatusService = $this->createPaymentStatusService();

        if ($expectsException) {
            static::assertTrue(\is_string($exceptionClassString), 'Parameter "exceptionClassString" is requred if this test expects a exception');

            $this->expectException($exceptionClassString);
        }

        $paymentStatusService->updatePaymentStatusV2($shopwareOrderId, $paymentStateId);

        if ($expectsException) {
            return;
        }

        /** @var Order $order */
        $order = $this->getContainer()->get('models')->getRepository(Order::class)->find($shopwareOrderId);

        static::assertSame($paymentStateId, $order->getPaymentStatus()->getId());
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function UpdatePaymentStatusV2TestDataProvider()
    {
        yield 'Expects OrderNotFoundException because order does not exist' => [
            self::ANY_ID,
            self::ANY_ID,
            self::EXPECTS_EXCEPTION,
            OrderNotFoundException::class,
        ];

        yield 'Expects UnexpectedValueException because payment status does not exist' => [
            self::SHOPWARE_ORDER_ID,
            self::ANY_ID,
            self::EXPECTS_EXCEPTION,
            UnexpectedValueException::class,
        ];

        yield 'Payment status should be updated' => [
            self::SHOPWARE_ORDER_ID,
            Status::PAYMENT_STATE_COMPLETELY_PAID,
            self::EXPECTS_NO_EXCEPTION,
        ];
    }

    /**
     * @return PaymentStatusService
     */
    private function createPaymentStatusService()
    {
        return new PaymentStatusService(
            $this->getContainer()->get('models'),
            $this->createMock(LoggerService::class)
        );
    }
}
