<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Backend;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Backend\CaptureService;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\AuthorizationResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\CaptureResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\OrderResource;

class CaptureServiceTest extends TestCase
{
    /**
     * @var MockObject|ExceptionHandlerServiceInterface
     */
    private $exceptionHandler;

    /**
     * @var MockObject|OrderResource
     */
    private $orderResource;

    /**
     * @var MockObject|AuthorizationResource
     */
    private $authorizationResource;

    /**
     * @var MockObject|CaptureResource
     */
    private $captureResource;

    /**
     * @var MockObject|PaymentStatusService
     */
    private $paymentStatusService;

    /**
     * @before
     *
     * @return void
     */
    public function init()
    {
        $this->exceptionHandler = static::createMock(ExceptionHandlerServiceInterface::class);
        $this->orderResource = static::createMock(OrderResource::class);
        $this->authorizationResource = static::createMock(AuthorizationResource::class);
        $this->captureResource = static::createMock(CaptureResource::class);
        $this->paymentStatusService = static::createMock(PaymentStatusService::class);
    }

    /**
     * @return void
     */
    public function testCaptureOrderExecutesCapture()
    {
        $orderId = '2a4ceaf1-9d6a-49cf-af93-5738617dd6bc';

        $this->givenTheOrderIsCreatedSuccessfully();
        $this->expectTheCaptureToBeExecutedForOrder($orderId);

        $this->getCaptureService()->captureOrder(
            $orderId,
            '123',
            'EUR',
            false
        );
    }

    /**
     * @return CaptureService
     */
    private function getCaptureService(
        ExceptionHandlerServiceInterface $exceptionHandler = null,
        OrderResource $orderResource = null,
        AuthorizationResource $authorizationResource = null,
        CaptureResource $captureResource = null,
        PaymentStatusService $paymentStatusService = null
    ) {
        return new CaptureService(
            $exceptionHandler ?: $this->exceptionHandler,
            $orderResource ?: $this->orderResource,
            $authorizationResource ?: $this->authorizationResource,
            $captureResource ?: $this->captureResource,
            $paymentStatusService ?: $this->paymentStatusService
        );
    }

    /**
     * @param string $orderId
     *
     * @return void
     */
    private function expectTheCaptureToBeExecutedForOrder($orderId)
    {
        $this->orderResource->expects(static::once())
            ->method('capture')
            ->with($orderId, static::anything());
    }

    /**
     * @return void
     */
    private function givenTheOrderIsCreatedSuccessfully()
    {
        $this->orderResource->method('capture')
            ->willReturn([]);
    }
}
