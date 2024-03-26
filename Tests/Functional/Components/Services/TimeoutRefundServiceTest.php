<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use DateTime;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Exception\TimeoutInfoException;
use SwagPaymentPayPalUnified\Components\Services\TimeoutRefundService;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\CaptureResource;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class TimeoutRefundServiceTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testSaveInfo()
    {
        $testPayPalOrderId = 'testPayPalOrderId';
        $testOrderAmount = 100.0;

        $this->createTimeoutRefundService()->saveInfo($testPayPalOrderId, $testOrderAmount);

        $result = $this->getContainer()
            ->get('dbal_connection')
            ->fetchAssoc('SELECT * FROM swag_payment_paypal_unified_order_refund_info WHERE paypal_order_id = :paypalOrderId', ['paypalOrderId' => $testPayPalOrderId]);

        static::assertNotEmpty($result);
        static::assertSame($testPayPalOrderId, $result['paypal_order_id']);
        static::assertSame($testOrderAmount, (float) $result['order_amount']);
    }

    /**
     * @return void
     */
    public function testDeleteInfo()
    {
        $timeoutRefundService = $this->createTimeoutRefundService();
        static::assertTrue($this->checkInfoTableIsEmpty());

        $timeoutRefundService->saveInfo('testPayPalOrderId', 100.0);
        static::assertFalse($this->checkInfoTableIsEmpty());

        $timeoutRefundService->deleteInfo('testPayPalOrderId');
        static::assertTrue($this->checkInfoTableIsEmpty());
    }

    /**
     * @return void
     */
    public function testRefund()
    {
        $payPalOrderId = 'testPayPalOrderId';
        $captureId = 'testCaptureId';
        $orderAmount = 100.0;
        $currency = 'EUR';

        $captureResourceMock = $this->createMock(CaptureResource::class);
        $captureResourceMock->expects(static::once())
            ->method('refund');

        $this->getContainer()->get('dbal_connection')->insert('swag_payment_paypal_unified_order_refund_info', [
            'paypal_order_id' => $payPalOrderId,
            'order_amount' => (string) $orderAmount,
            'currency' => $currency,
            'created_at' => (string) (new DateTime())->getTimestamp(),
        ]);

        $this->createTimeoutRefundService($captureResourceMock)->refund($payPalOrderId, $captureId);
    }

    /**
     * @return void
     */
    public function testRefundShouldThrowTimeoutInfoException()
    {
        $this->expectException(TimeoutInfoException::class);
        $this->expectExceptionMessage('Timeout information not found for PayPal order with ID: payPalOrderId');

        $this->createTimeoutRefundService()->refund('payPalOrderId', 'captureId');
    }

    /**
     * @return TimeoutRefundService
     */
    private function createTimeoutRefundService(CaptureResource $captureResource = null)
    {
        if ($captureResource === null) {
            $captureResource = $this->createMock(CaptureResource::class);
        }

        return new TimeoutRefundService(
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $captureResource
        );
    }

    /**
     * @return bool
     */
    private function checkInfoTableIsEmpty()
    {
        $result = (bool) $this->getContainer()->get('dbal_connection')->fetchColumn('SELECT COUNT(id) FROM swag_payment_paypal_unified_order_refund_info');

        return !$result;
    }
}
