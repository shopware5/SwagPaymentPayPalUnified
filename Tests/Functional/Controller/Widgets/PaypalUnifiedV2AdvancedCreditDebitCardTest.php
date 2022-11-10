<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Widgets;

use Enlight_Controller_Request_RequestHttp;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_View_Default;
use Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception\ThreeDSecureExceptionDescription;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

class PaypalUnifiedV2AdvancedCreditDebitCardTest extends PaypalPaymentControllerTestCase
{
    /**
     * @return void
     */
    public function testCaptureActionLogIfLiabilityShiftIsNotPossible()
    {
        $request = new Enlight_Controller_Request_RequestHttp();
        $request->setHeader('X-Requested-With', 'XMLHttpRequest');
        $request->setParam('paypalOrderId', '123456789');

        // this is just for Shopware 5.6 tests
        if ($request->headers instanceof HeaderBag) {
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        }

        $payPalOrder = $this->createPaypalOrder();

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->expects(static::once())->method('get')->willReturn($payPalOrder);

        $redirectDataBuilderMock = $this->createMock(RedirectDataBuilder::class);
        $redirectDataBuilderMock->expects(static::once())->method('setCode')->willReturnSelf();
        $redirectDataBuilderMock->expects(static::once())->method('setException')->willReturnSelf();
        $redirectDataBuilderMock->expects(static::once())->method('hasException')->willReturn(true);
        $redirectDataBuilderMock->expects(static::once())->method('getCode')->willReturn(ThreeDSecureExceptionDescription::STATUS_CODE___UNKNOWN);

        $redirectDataBuilderFactoryMock = $this->createMock(RedirectDataBuilderFactory::class);
        $redirectDataBuilderFactoryMock->method('createRedirectDataBuilder')->willReturn($redirectDataBuilderMock);

        $controller = $this->getController(
            Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard::class,
            [
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY => $redirectDataBuilderFactoryMock,
                self::SERVICE_THREE_D_SECURE_RESULT_CHECKER => $this->getContainer()->get('paypal_unified.three_d_secure_result_checker'),
                self::SERVICE_PAYMENT_CONTROLLER_HELPER => $this->getContainer()->get('paypal_unified.payment_controller_helper'),
            ],
            $request,
            new Enlight_Controller_Response_ResponseTestCase(),
            new Enlight_View_Default($this->getContainer()->get('template'))
        );

        $controller->View()->addTemplateDir(__DIR__ . '/../../../../Resources/views/');
        $controller->View()->addTemplateDir($this->getContainer()->getParameter('kernel.root_dir') . '/themes/Frontend/Bare/');

        $controller->captureAction();

        static::assertSame(400, $controller->Response()->getHttpResponseCode());
        static::assertSame(ThreeDSecureExceptionDescription::STATUS_CODE___UNKNOWN, (int) $controller->View()->getAssign('paypalUnifiedErrorCode'));

        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('<div class="paypal-unified--error">', $controller->View()->getAssign('errorTemplate'));

            return;
        }

        static::assertContains('<div class="paypal-unified--error">', $controller->View()->getAssign('errorTemplate'));
    }

    /**
     * @return Order
     */
    private function createPaypalOrder()
    {
        $authenticationResult = new AuthenticationResult();
        $authenticationResult->setLiabilityShift(AuthenticationResult::LIABILITY_SHIFT_UNKNOWN);

        $card = new Card();
        $card->setAuthenticationResult($authenticationResult);

        $paymentSource = new PaymentSource();
        $paymentSource->setCard($card);

        $payPalOrder = new Order();
        $payPalOrder->setPaymentSource($paymentSource);

        return $payPalOrder;
    }
}
