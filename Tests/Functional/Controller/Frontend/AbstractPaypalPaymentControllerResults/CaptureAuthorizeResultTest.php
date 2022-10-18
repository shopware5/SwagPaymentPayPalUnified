<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\AbstractPaypalPaymentControllerResults;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentControllerResults\CaptureAuthorizeResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

class CaptureAuthorizeResultTest extends TestCase
{
    /**
     * @return void
     */
    public function testCaptureAuthorizeResultWithNoParameter()
    {
        $captureAuthorizeResult = new CaptureAuthorizeResult();

        static::assertFalse($captureAuthorizeResult->getRequireRestart());
        static::assertFalse($captureAuthorizeResult->getPayerActionRequired());
        static::assertFalse($captureAuthorizeResult->getInstrumentDeclined());
        static::assertNull($captureAuthorizeResult->getOrder());
    }

    /**
     * @return void
     */
    public function testCaptureAuthorizeResultWithRequireRestart()
    {
        $captureAuthorizeResult = new CaptureAuthorizeResult(CaptureAuthorizeResult::REQUIRE_RESTART, true);

        static::assertTrue($captureAuthorizeResult->getRequireRestart());

        static::assertFalse($captureAuthorizeResult->getPayerActionRequired());
        static::assertFalse($captureAuthorizeResult->getInstrumentDeclined());
        static::assertNull($captureAuthorizeResult->getOrder());
    }

    /**
     * @return void
     */
    public function testCaptureAuthorizeResultWithPayerActionRequired()
    {
        $captureAuthorizeResult = new CaptureAuthorizeResult(CaptureAuthorizeResult::PAYER_ACTION_REQUIRED, true);

        static::assertTrue($captureAuthorizeResult->getPayerActionRequired());

        static::assertFalse($captureAuthorizeResult->getRequireRestart());
        static::assertFalse($captureAuthorizeResult->getInstrumentDeclined());
        static::assertNull($captureAuthorizeResult->getOrder());
    }

    /**
     * @return void
     */
    public function testCaptureAuthorizeResultWithInstrumentDeclined()
    {
        $captureAuthorizeResult = new CaptureAuthorizeResult(CaptureAuthorizeResult::INSTRUMENT_DECLINED, true);

        static::assertTrue($captureAuthorizeResult->getInstrumentDeclined());

        static::assertFalse($captureAuthorizeResult->getPayerActionRequired());
        static::assertFalse($captureAuthorizeResult->getRequireRestart());
        static::assertNull($captureAuthorizeResult->getOrder());
    }

    /**
     * @return void
     */
    public function testCaptureAuthorizeResultWitOrder()
    {
        $captureAuthorizeResult = new CaptureAuthorizeResult(CaptureAuthorizeResult::ORDER, new Order());

        static::assertInstanceOf(Order::class, $captureAuthorizeResult->getOrder());

        static::assertFalse($captureAuthorizeResult->getInstrumentDeclined());
        static::assertFalse($captureAuthorizeResult->getPayerActionRequired());
        static::assertFalse($captureAuthorizeResult->getRequireRestart());
    }
}
