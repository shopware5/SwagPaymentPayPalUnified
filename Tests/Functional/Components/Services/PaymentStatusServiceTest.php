<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use Generator;
use PDO;
use PHPUnit\Framework\TestCase;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Services\LoggerService;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class PaymentStatusServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use ContainerTrait;
    use SettingsHelperTrait;
    use ShopRegistrationTrait;

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
        $isCalled = false;
        $callback = function () use (&$isCalled) {
            $isCalled = true;
        };

        $this->getContainer()->get('events')->addListener('Shopware_Controllers_Backend_OrderState_Send_BeforeSend', $callback);

        $sql = file_get_contents(__DIR__ . '/_fixtures/order_payment_state_test.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->exec($sql);

        $paymentStatusService = $this->createPaymentStatusService();

        if ($expectsException) {
            static::assertIsString($exceptionClassString, 'Parameter "exceptionClassString" is requred if this test expects a exception');

            $this->expectException($exceptionClassString);
        }

        $paymentStatusService->updatePaymentStatusV2($shopwareOrderId, $paymentStateId);

        if ($expectsException) {
            return;
        }

        static::assertTrue($isCalled);
        $modelManager = $this->getContainer()->get('models');
        $modelManager->clear(Order::class);
        /** @var Order $order */
        $order = $modelManager->getRepository(Order::class)->find($shopwareOrderId);

        static::assertSame($paymentStateId, $order->getPaymentStatus()->getId());
        static::assertNotNull($order->getClearedDate());
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function UpdatePaymentStatusV2TestDataProvider()
    {
        yield 'Payment status should be updated' => [
            self::SHOPWARE_ORDER_ID,
            Status::PAYMENT_STATE_COMPLETELY_PAID,
            self::EXPECTS_NO_EXCEPTION,
        ];
    }

    /**
     * @return void
     */
    public function testSetOrderAndPaymentStatusForFailedOrder()
    {
        $orderNumber = '29008';

        $sql = file_get_contents(__DIR__ . '/_fixtures/update_order_payment_status.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $this->insertGeneralSettingsFromArray([
            'active' => 1,
            'order_status_on_failed_payment' => 1000,
            'payment_status_on_failed_payment' => 1000,
        ]);

        $paymentStatusService = $this->createPaymentStatusService();

        $paymentStatusService->setOrderAndPaymentStatusForFailedOrder($orderNumber);

        $result = $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->select(['status', 'cleared'])
            ->from('s_order')
            ->where('ordernumber = :orderNumber')
            ->setParameter('orderNumber', $orderNumber)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);

        static::assertSame('1000', $result['status']);
        static::assertSame('1000', $result['cleared']);
    }

    /**
     * @dataProvider determinePaymentStausForCapturingTestDataProvider
     *
     * @param bool  $finalize
     * @param float $amountToCapture
     * @param float $maxCaptureAmount
     * @param int   $expectedResult
     *
     * @return void
     */
    public function testDeterminePaymentStausForCapturing($finalize, $amountToCapture, $maxCaptureAmount, $expectedResult)
    {
        $service = $this->createPaymentStatusService();

        $result = $service->determinePaymentStausForCapturing($finalize, $amountToCapture, $maxCaptureAmount);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function determinePaymentStausForCapturingTestDataProvider()
    {
        yield 'Is finalized' => [
            true,
            0.00,
            0.00,
            Status::PAYMENT_STATE_COMPLETELY_PAID,
        ];

        yield 'Not finalized amountToCapture is less than maxCaptureAmount' => [
            false,
            5.00,
            6.00,
            Status::PAYMENT_STATE_PARTIALLY_PAID,
        ];

        yield 'Not finalized amountToCapture equals maxCaptureAmount' => [
            false,
            5.00,
            5.00,
            Status::PAYMENT_STATE_COMPLETELY_PAID,
        ];

        yield 'Not finalized amountToCapture is larger than maxCaptureAmount' => [
            false,
            6.00,
            5.00,
            Status::PAYMENT_STATE_COMPLETELY_PAID,
        ];
    }

    /**
     * @return void
     */
    public function testUpdatePaymentStatusV2IsSendMail()
    {
        $this->insertGeneralSettingsFromArray(['active' => 1]);

        $paymentStatusService = $this->createPaymentStatusService();

        $isCalled = false;
        $callback = function () use (&$isCalled) {
            $isCalled = true;
        };

        $this->getContainer()->get('events')->addListener('Enlight_Components_Mail_Send', $callback);

        $paymentStatusService->setOrderAndPaymentStatusForFailedOrder('20001');

        static::assertTrue($isCalled);
    }

    /**
     * @return PaymentStatusService
     */
    private function createPaymentStatusService()
    {
        return new PaymentStatusService(
            $this->getContainer()->get('models'),
            $this->createMock(LoggerService::class),
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('paypal_unified.dependency_provider'),
            $this->getContainer()->get('config')
        );
    }
}
