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
use Generator;
use Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception\ThreeDSecureExceptionDescription;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult\ThreeDSecure;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\AssertStringContainsTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

class PaypalUnifiedV2AdvancedCreditDebitCardThreeDSecureTest extends PaypalPaymentControllerTestCase
{
    use AssertStringContainsTrait;

    /**
     * @dataProvider captureActionCheckThreeDSecureStatusTestDataProvider
     *
     * @param int $expectedCode
     *
     * @return void
     */
    public function testCaptureActionCheckThreeDSecureStatus(Order $paypalOrder, $expectedCode)
    {
        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($paypalOrder);

        $controller = $this->getController(
            Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard::class,
            [
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_THREE_D_SECURE_RESULT_CHECKER => $this->getContainer()->get('paypal_unified.three_d_secure_result_checker'),
                self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY => $this->getContainer()->get('paypal_unified.redirect_data_builder_factory'),
                self::SERVICE_PAYMENT_CONTROLLER_HELPER => $this->getContainer()->get('paypal_unified.payment_controller_helper'),
            ],
            new Enlight_Controller_Request_RequestHttp(),
            new Enlight_Controller_Response_ResponseTestCase(),
            new Enlight_View_Default($this->getContainer()->get('template'))
        );

        $controller->Request()->setParam('token', 'any');
        $controller->Request()->setParam('threeDSecureRetry', 3);
        $controller->Request()->setHeader('X-Requested-With', 'XMLHttpRequest');

        // this is just for Shopware 5.6 tests
        if ($controller->Request()->headers instanceof HeaderBag) {
            $controller->Request()->headers->set('X-Requested-With', 'XMLHttpRequest');
        }

        $controller->View()->addTemplateDir(__DIR__ . '/../../../../Resources/views/');
        $controller->View()->addTemplateDir($this->getContainer()->getParameter('kernel.root_dir') . '/themes/Frontend/Bare/');

        $controller->captureAction();

        static::assertSame(400, $controller->Response()->getHttpResponseCode());
        static::assertSame($expectedCode, (int) $controller->View()->getAssign('paypalUnifiedErrorCode'));

        static::assertStringContains($this, '<div class="paypal-unified--error">', $controller->View()->getAssign('errorTemplate'));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function captureActionCheckThreeDSecureStatusTestDataProvider()
    {
        yield 'Test case 1 EnrollmentStatus Y Authentication_Status N LiabilityShift NO' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                ThreeDSecure::AUTHENTICATION_STATUS_N,
                AuthenticationResult::LIABILITY_SHIFT_NO
            ),
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_N_NO,
        ];

        yield 'Test case 2 EnrollmentStatus Y Authentication_Status R LiabilityShift NO' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                ThreeDSecure::AUTHENTICATION_STATUS_R,
                AuthenticationResult::LIABILITY_SHIFT_NO
            ),
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_R_NO,
        ];

        yield 'Test case 3 EnrollmentStatus Y Authentication_Status U LiabilityShift UNKNOWN' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                ThreeDSecure::AUTHENTICATION_STATUS_U,
                AuthenticationResult::LIABILITY_SHIFT_UNKNOWN
            ),
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_U_UNKNOWN,
        ];

        yield 'Test case 4 EnrollmentStatus Y Authentication_Status U LiabilityShift NO' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                ThreeDSecure::AUTHENTICATION_STATUS_U,
                AuthenticationResult::LIABILITY_SHIFT_NO
            ),
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_U_NO,
        ];

        yield 'Test case 5 EnrollmentStatus Y Authentication_Status C LiabilityShift UNKNOWN' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                ThreeDSecure::AUTHENTICATION_STATUS_C,
                AuthenticationResult::LIABILITY_SHIFT_UNKNOWN
            ),
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_C_UNKNOWN,
        ];

        yield 'Test case 6 EnrollmentStatus Y LiabilityShift NO' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                'ANY',
                AuthenticationResult::LIABILITY_SHIFT_NO
            ),
            ThreeDSecureExceptionDescription::STATUS_CODE_Y__NO,
        ];

        yield 'Test case 7 EnrollmentStatus U LiabilityShift UNKNOWN' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_U,
                'ANY',
                AuthenticationResult::LIABILITY_SHIFT_UNKNOWN
            ),
            ThreeDSecureExceptionDescription::STATUS_CODE_U__UNKNOWN,
        ];

        yield 'Test case 8 LiabilityShift UNKNOWN' => [
            $this->createPayPalOrder(
                'ANY',
                'ANY',
                AuthenticationResult::LIABILITY_SHIFT_UNKNOWN
            ),
            ThreeDSecureExceptionDescription::STATUS_CODE___UNKNOWN,
        ];

        yield 'Test case 8 unknown status' => [
            $this->createPayPalOrder(
                'ANY',
                'ANY',
                'ANY'
            ),
            ThreeDSecureExceptionDescription::STATUS_CODE_DEFAULT,
        ];
    }

    /**
     * @param string $enrollmentStatus
     * @param string $authenticationStatus
     * @param string $liabilityShift
     *
     * @return Order
     */
    private function createPayPalOrder($enrollmentStatus, $authenticationStatus, $liabilityShift)
    {
        $threeDSecure = new ThreeDSecure();
        $threeDSecure->setEnrollmentStatus($enrollmentStatus);
        $threeDSecure->setAuthenticationStatus($authenticationStatus);

        $authenticationResult = new AuthenticationResult();
        $authenticationResult->setLiabilityShift($liabilityShift);
        $authenticationResult->setThreeDSecure($threeDSecure);

        $card = new Card();
        $card->setAuthenticationResult($authenticationResult);

        $paymentSource = new PaymentSource();
        $paymentSource->setCard($card);

        $order = new Order();
        $order->setPaymentSource($paymentSource);

        return $order;
    }
}
