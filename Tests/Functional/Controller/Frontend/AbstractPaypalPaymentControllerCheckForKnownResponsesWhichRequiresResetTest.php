<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentControllerResults\CaptureAuthorizeResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\Tests\Functional\AssertLocationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPaypalPaymentControllerCheckForKnownResponsesWhichRequiresResetTest extends PaypalPaymentControllerTestCase
{
    use ReflectionHelperTrait;
    use AssertLocationTrait;

    /**
     * @return void
     */
    public function testCheckForKnownResponsesWhichRequiresResetWithPayActionRequiredShouldRedirect()
    {
        $controller = $this->getAbstractController();

        $captureAuthorizationResult = new CaptureAuthorizeResult(false, null, true);

        $checkForKnownResponsesWhichRequiresResetMethod = $this->getReflectionMethod(
            AbstractPaypalPaymentController::class,
            'checkForKnownResponsesWhichRequiresReset'
        );

        $methodResult = $checkForKnownResponsesWhichRequiresResetMethod->invoke($controller, $captureAuthorizationResult);
        $statusCodeResult = $controller->Response()->getHttpResponseCode();

        static::assertTrue($methodResult);
        static::assertSame(302, $statusCodeResult);
        static::assertLocationEndsWith($controller->Response(), '/checkout/confirm/payerActionRequired/1/payerInstrumentDeclined/0');
    }

    /**
     * @return void
     */
    public function testCheckForKnownResponsesWhichRequiresResetWithInstrumentDeclinedShouldRedirect()
    {
        $controller = $this->getAbstractController();

        $captureAuthorizationResult = new CaptureAuthorizeResult(false, null, false, true);

        $checkForKnownResponsesWhichRequiresResetMethod = $this->getReflectionMethod(
            AbstractPaypalPaymentController::class,
            'checkForKnownResponsesWhichRequiresReset'
        );

        $methodResult = $checkForKnownResponsesWhichRequiresResetMethod->invoke($controller, $captureAuthorizationResult);
        $statusCodeResult = $controller->Response()->getHttpResponseCode();

        static::assertTrue($methodResult);
        static::assertSame(302, $statusCodeResult);
        static::assertLocationEndsWith($controller->Response(), '/checkout/confirm/payerActionRequired/0/payerInstrumentDeclined/1');
    }

    /**
     * @return void
     */
    public function testCheckForKnownResponsesWhichRequiresResetShouldReturnFalse()
    {
        $controller = $this->getAbstractController();

        $captureAuthorizationResult = new CaptureAuthorizeResult(false, new Order());

        $checkForKnownResponsesWhichRequiresResetMethod = $this->getReflectionMethod(
            AbstractPaypalPaymentController::class,
            'checkForKnownResponsesWhichRequiresReset'
        );

        $result = $checkForKnownResponsesWhichRequiresResetMethod->invoke($controller, $captureAuthorizationResult);

        static::assertFalse($result);
    }

    /**
     * @return AbstractPaypalPaymentController
     */
    private function getAbstractController()
    {
        $controller = $this->getController(
            AbstractPaypalPaymentController::class,
            [],
            new Enlight_Controller_Request_RequestTestCase(),
            new Enlight_Controller_Response_ResponseTestCase()
        );

        static::assertInstanceOf(AbstractPaypalPaymentController::class, $controller);

        return $controller;
    }
}
