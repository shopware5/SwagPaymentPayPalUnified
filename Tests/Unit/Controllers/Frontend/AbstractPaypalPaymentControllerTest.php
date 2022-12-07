<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Controllers\Frontend;

use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPaypalPaymentControllerTest extends PaypalPaymentControllerTestCase
{
    /**
     * @param string $paypalErrorCode
     * @param string $shopwareErrorCode
     *
     * @dataProvider cancelActionErrorCodeProvider
     *
     * @return void
     */
    public function testCancelActionUsesExpectedErrorCodes($paypalErrorCode, $shopwareErrorCode)
    {
        $this->givenThePaypalErrorCodeEquals($paypalErrorCode);
        $this->expectTheShopwareErrorCodeToBe($shopwareErrorCode);

        $this->getController(TestPaypalPaymentController::class, [])->cancelAction();
    }

    /**
     * @return array<string,array<string|int>>
     */
    public function cancelActionErrorCodeProvider()
    {
        return [
            'Unknown error code' => [
                'badcd139-2df3-4a12-89e0-0e0ec176843f',
                ErrorCodes::CANCELED_BY_USER,
            ],
            'Processing error' => [
                'processing_error',
                ErrorCodes::COMMUNICATION_FAILURE,
            ],
        ];
    }

    /**
     * @param string $errorCode
     *
     * @return void
     */
    protected function expectTheShopwareErrorCodeToBe($errorCode)
    {
        $redirectDataBuilder = $this->getMockedService(self::SERVICE_REDIRECT_DATA_BUILDER);

        $redirectDataBuilder->expects(static::once())
            ->method('setCode')
            ->with($errorCode);
    }

    /**
     * @param string $errorCode
     *
     * @return void
     */
    protected function givenThePaypalErrorCodeEquals($errorCode)
    {
        $this->request->method('getParam')
            ->with('errorcode')
            ->willReturn($errorCode);
    }

    /**
     * @param string $checkoutType
     *
     * @return void
     */
    protected function givenTheCheckoutTypeEquals($checkoutType)
    {
        $this->request->method('getParam')
            ->willReturnMap([
                [$checkoutType, false, true],
                [static::anything(), false, false],
            ]);
    }
}

class TestPaypalPaymentController extends AbstractPaypalPaymentController
{
}
