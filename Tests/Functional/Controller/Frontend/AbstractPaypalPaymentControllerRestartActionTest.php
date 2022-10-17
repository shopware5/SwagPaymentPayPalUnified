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
use SwagPaymentPayPalUnified\Tests\Functional\AssertLocationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPaypalPaymentControllerRestartActionTest extends PaypalPaymentControllerTestCase
{
    use ReflectionHelperTrait;
    use AssertLocationTrait;

    /**
     * @return void
     */
    public function testRestartActionUseInContext()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $abstractController = $this->getController(AbstractPaypalPaymentController::class, [], $request, $response);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, 'restartAction');

        $reflectionMethod->invokeArgs(
            $abstractController,
            [
                true,
                '123456789',
                'frontend',
                'PaypalUnifiedV2',
                'return',
            ]
        );

        static::assertSame(302, $response->getHttpResponseCode());
        static::assertLocationEndsWith($response, '/PaypalUnifiedV2/return/paypalOrderId/123456789/inContextCheckout/1');
    }

    /**
     * @return void
     */
    public function testRestartActionNotUseInContext()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $abstractController = $this->getController(AbstractPaypalPaymentController::class, [], $request, $response);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, 'restartAction');

        $reflectionMethod->invokeArgs(
            $abstractController,
            [
                false,
                '123456789',
                'frontend',
                'PaypalUnifiedV2',
                'return',
            ]
        );

        static::assertSame(302, $response->getHttpResponseCode());
        static::assertLocationEndsWith($response, '/PaypalUnifiedV2/return/token/123456789');
    }
}
