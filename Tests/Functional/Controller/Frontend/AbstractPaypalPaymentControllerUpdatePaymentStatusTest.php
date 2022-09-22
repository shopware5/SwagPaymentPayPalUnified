<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Generator;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPaypalPaymentControllerUpdatePaymentStatusTest extends PaypalPaymentControllerTestCase
{
    use ReflectionHelperTrait;

    /**
     * @dataProvider updatePaymentStatusTestDataProvider
     *
     * @param string $intent
     *
     * @return void
     */
    public function testUpdatePaymentStatus($intent)
    {
        $expectedParameter = Status::PAYMENT_STATE_COMPLETELY_PAID;
        if ($intent !== PaymentIntentV2::CAPTURE) {
            $expectedParameter = Status::PAYMENT_STATE_RESERVED;
        }

        $paymentStatusServiceMock = $this->createMock(PaymentStatusService::class);
        $paymentStatusServiceMock->expects(static::once())->method('updatePaymentStatusV2')
            ->with(
                1234,
                $expectedParameter
            );

        $abstractController = $this->getController(AbstractPaypalPaymentController::class, [
            self::SERVICE_PAYMENT_STATUS_SERVICE => $paymentStatusServiceMock,
        ]);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, 'updatePaymentStatus');

        $reflectionMethod->invokeArgs($abstractController, [$intent, 1234]);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function updatePaymentStatusTestDataProvider()
    {
        yield 'CAPTURE' => [
            PaymentIntentV2::CAPTURE,
        ];

        yield 'AUTHORIZE' => [
            PaymentIntentV2::AUTHORIZE,
        ];

        yield 'other' => [
            'other',
        ];
    }
}
