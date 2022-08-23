<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Backend;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SwagPaymentPayPalUnified\Components\Backend\PaymentDetailsService;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\Services\Legacy\LegacyService;
use SwagPaymentPayPalUnified\Components\Services\TransactionHistoryBuilderService;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\SaleResource;

class PaymentDetailsServiceAssignDataToArrayTest extends TestCase
{
    /**
     * @return void
     */
    public function testPrepareUnifiedDetailsShouldAssignAllNecessaryData()
    {
        $paymentResource = $this->createMock(PaymentResource::class);
        $paymentResource->expects(static::once())->method('get')->willReturn($this->getPaymentDetailsArray());

        $paymentDetailsService = $this->createPaymentDetailService(null, $paymentResource);

        $reflectionMethod = (new ReflectionClass(PaymentDetailsService::class))->getMethod('prepareUnifiedDetails');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($paymentDetailsService, 'anyId');

        static::assertSame('orderData', $result[PaymentDetailsService::TRANSACTION_ORDER]);
        static::assertSame('authorizationData', $result[PaymentDetailsService::TRANSACTION_AUTHORIZATION]);
        static::assertSame('saleData', $result[PaymentDetailsService::TRANSACTION_SALE]);
    }

    /**
     * @return array<string,array<int,array<string,array<int,mixed>>>>
     */
    private function getPaymentDetailsArray()
    {
        return [
            'transactions' => [
                [
                    'related_resources' => [
                        ['anyData' => []],
                        ['anyOtherData' => []],
                        [PaymentDetailsService::TRANSACTION_ORDER => 'orderData'],
                        [PaymentDetailsService::TRANSACTION_AUTHORIZATION => 'authorizationData'],
                        [PaymentDetailsService::TRANSACTION_SALE => 'saleData'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return PaymentDetailsService
     */
    private function createPaymentDetailService(
        ExceptionHandlerServiceInterface $exceptionHandler = null,
        PaymentResource $paymentResource = null,
        SaleResource $saleResource = null,
        LegacyService $legacyService = null,
        TransactionHistoryBuilderService $transactionHistoryBuilder = null
    ) {
        return new PaymentDetailsService(
            $exceptionHandler === null ? $this->createMock(ExceptionHandlerServiceInterface::class) : $exceptionHandler,
            $paymentResource === null ? $this->createMock(PaymentResource::class) : $paymentResource,
            $saleResource === null ? $this->createMock(SaleResource::class) : $saleResource,
            $legacyService === null ? $this->createMock(LegacyService::class) : $legacyService,
            $transactionHistoryBuilder === null ? $this->createMock(TransactionHistoryBuilderService::class) : $transactionHistoryBuilder
        );
    }
}
