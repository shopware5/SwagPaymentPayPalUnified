<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\ThreeDSecureResultChecker;

use Exception;
use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception\ThreeDSecureExceptionDescription;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\ThreeDSecureResultChecker;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult\ThreeDSecure;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;

class ThreeDSecureResultCheckerTest extends TestCase
{
    use ReflectionHelperTrait;

    /**
     * @dataProvider checkStausTestDataProvider
     *
     * @param bool $expectResult
     * @param int  $expectedExceptionCode
     *
     * @return void
     */
    public function testcheckStaus(Order $payPalOrder, $expectResult, $expectedExceptionCode = 0)
    {
        $threeDSecureChecker = $this->getService();

        if (!$expectResult) {
            $this->expectException(Exception::class);
            $this->expectExceptionCode($expectedExceptionCode);

            if (\method_exists($this, 'expectExceptionMessageMatches')) {
                $this->expectExceptionMessageMatches(
                    '/^.* REASON: ' . ThreeDSecureExceptionDescription::getDescriptionByCode($expectedExceptionCode) . '.*$/'
                );
            }
        }

        $result = $threeDSecureChecker->checkStaus($payPalOrder);

        if ($expectResult) {
            static::assertTrue($result);
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function checkStausTestDataProvider()
    {
        yield 'Test case 1 EnrollmentStatus Y Authentication_Status Y LiabilityShift POSSIBLE' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                ThreeDSecure::AUTHENTICATION_STATUS_Y,
                AuthenticationResult::LIABILITY_SHIFT_POSSIBLE
            ),
            true,
        ];

        yield 'Test case 2 EnrollmentStatus Y Authentication_Status N LiabilityShift NO' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                ThreeDSecure::AUTHENTICATION_STATUS_N,
                AuthenticationResult::LIABILITY_SHIFT_NO
            ),
            false,
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_N_NO,
        ];

        yield 'Test case 3 EnrollmentStatus Y Authentication_Status R LiabilityShift NO' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                ThreeDSecure::AUTHENTICATION_STATUS_R,
                AuthenticationResult::LIABILITY_SHIFT_NO
            ),
            false,
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_R_NO,
        ];

        yield 'Test case 4 EnrollmentStatus Y Authentication_Status A LiabilityShift POSSIBLE' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                ThreeDSecure::AUTHENTICATION_STATUS_A,
                AuthenticationResult::LIABILITY_SHIFT_POSSIBLE
            ),
            true,
        ];

        yield 'Test case 5 EnrollmentStatus Y Authentication_Status U LiabilityShift UNKNOWN' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                ThreeDSecure::AUTHENTICATION_STATUS_U,
                AuthenticationResult::LIABILITY_SHIFT_UNKNOWN
            ),
            false,
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_U_UNKNOWN,
        ];

        yield 'Test case 6 EnrollmentStatus Y Authentication_Status U LiabilityShift NO' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                ThreeDSecure::AUTHENTICATION_STATUS_U,
                AuthenticationResult::LIABILITY_SHIFT_NO
            ),
            false,
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_U_NO,
        ];

        yield 'Test case 7 EnrollmentStatus Y Authentication_Status C LiabilityShift UNKNOWN' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                ThreeDSecure::AUTHENTICATION_STATUS_C,
                AuthenticationResult::LIABILITY_SHIFT_UNKNOWN
            ),
            false,
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_C_UNKNOWN,
        ];

        yield 'Test case 8 EnrollmentStatus Y LiabilityShift NO' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_Y,
                'ANY',
                AuthenticationResult::LIABILITY_SHIFT_NO
            ),
            false,
            ThreeDSecureExceptionDescription::STATUS_CODE_Y__NO,
        ];

        yield 'Test case 9 EnrollmentStatus N LiabilityShift NO' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_N,
                'ANY',
                AuthenticationResult::LIABILITY_SHIFT_NO
            ),
            true,
        ];

        yield 'Test case 10 EnrollmentStatus U LiabilityShift NO' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_U,
                'ANY',
                AuthenticationResult::LIABILITY_SHIFT_NO
            ),
            true,
        ];

        yield 'Test case 11 EnrollmentStatus U LiabilityShift UNKNOWN' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_U,
                'ANY',
                AuthenticationResult::LIABILITY_SHIFT_UNKNOWN
            ),
            false,
            ThreeDSecureExceptionDescription::STATUS_CODE_U__UNKNOWN,
        ];

        yield 'Test case 12 EnrollmentStatus B LiabilityShift NO' => [
            $this->createPayPalOrder(
                ThreeDSecure::ENROLLMENT_STATUS_B,
                'ANY',
                AuthenticationResult::LIABILITY_SHIFT_NO
            ),
            true,
        ];

        yield 'Test case 13 LiabilityShift UNKNOWN' => [
            $this->createPayPalOrder(
                'ANY',
                'ANY',
                AuthenticationResult::LIABILITY_SHIFT_UNKNOWN
            ),
            false,
            ThreeDSecureExceptionDescription::STATUS_CODE___UNKNOWN,
        ];
    }

    /**
     * @dataProvider get3DSecureTestDataProvider
     *
     * @param string|null $expectedResult
     *
     * @return void
     */
    public function testGet3DSecure(Order $order, $expectedResult)
    {
        $threeDSecureChecker = $this->getService();
        $reflectionMethod = $this->getReflectionMethod(ThreeDSecureResultChecker::class, 'get3DSecure');

        if ($expectedResult === null) {
            static::assertNull($reflectionMethod->invoke($threeDSecureChecker, $order));

            return;
        }

        static::assertInstanceOf(ThreeDSecure::class, $reflectionMethod->invoke($threeDSecureChecker, $order));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function get3DSecureTestDataProvider()
    {
        yield 'Without PaymentSource' => [
            $this->createPayPalOrderForPrivateMethods(),
            null,
        ];

        yield 'Without Card' => [
            $this->createPayPalOrderForPrivateMethods(true),
            null,
        ];

        yield 'Without AuthenticationResult' => [
            $this->createPayPalOrderForPrivateMethods(false, true),
            null,
        ];

        yield 'Without ThreeDSecure' => [
            $this->createPayPalOrderForPrivateMethods(false, false, true),
            null,
        ];

        yield 'With ThreeDSecure' => [
            $this->createPayPalOrderForPrivateMethods(false, false, false, true),
            true,
        ];
    }

    /**
     * @dataProvider getLiabilityShiftTestDataProvider
     *
     * @param string|null $expectedResult
     *
     * @return void
     */
    public function testGetLiabilityShift(Order $order, $expectedResult)
    {
        $threeDSecureChecker = $this->getService();
        $reflectionMethod = $this->getReflectionMethod(ThreeDSecureResultChecker::class, 'getLiabilityShift');

        static::assertSame($expectedResult, $reflectionMethod->invoke($threeDSecureChecker, $order));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getLiabilityShiftTestDataProvider()
    {
        yield 'Without PaymentSource' => [
            $this->createPayPalOrderForPrivateMethods(),
            null,
        ];

        yield 'Without Card' => [
            $this->createPayPalOrderForPrivateMethods(true),
            null,
        ];

        yield 'Without AuthenticationResult' => [
            $this->createPayPalOrderForPrivateMethods(false, true),
            null,
        ];

        yield 'With AuthenticationResult' => [
            $this->createPayPalOrderForPrivateMethods(false, false, true),
            AuthenticationResult::LIABILITY_SHIFT_POSSIBLE,
        ];
    }

    /**
     * @param bool $setPaymentSource
     * @param bool $setCard
     * @param bool $setThreeDSecure
     * @param bool $setAuthenticationResult
     *
     * @return Order
     */
    private function createPayPalOrderForPrivateMethods(
        $setPaymentSource = false,
        $setCard = false,
        $setAuthenticationResult = false,
        $setThreeDSecure = false
    ) {
        $order = new Order();

        if ($setPaymentSource) {
            $paymentSource = new PaymentSource();
            $order->setPaymentSource($paymentSource);
        }

        if ($setCard) {
            $paymentSource = new PaymentSource();
            $order->setPaymentSource($paymentSource);

            $card = new Card();
            $paymentSource->setCard($card);

            $order->setPaymentSource($paymentSource);
        }

        if ($setAuthenticationResult) {
            $authenticationResult = new AuthenticationResult();
            $authenticationResult->setLiabilityShift(AuthenticationResult::LIABILITY_SHIFT_POSSIBLE);

            $card = new Card();
            $card->setAuthenticationResult($authenticationResult);

            $paymentSource = new PaymentSource();
            $paymentSource->setCard($card);

            $order->setPaymentSource($paymentSource);
        }

        if ($setThreeDSecure) {
            $threeDSecure = new ThreeDSecure();
            $threeDSecure->setAuthenticationStatus(ThreeDSecure::AUTHENTICATION_STATUS_Y);
            $threeDSecure->setEnrollmentStatus(ThreeDSecure::ENROLLMENT_STATUS_Y);

            $authenticationResult = new AuthenticationResult();
            $authenticationResult->setLiabilityShift(AuthenticationResult::LIABILITY_SHIFT_POSSIBLE);
            $authenticationResult->setThreeDSecure($threeDSecure);

            $card = new Card();
            $card->setAuthenticationResult($authenticationResult);

            $paymentSource = new PaymentSource();
            $paymentSource->setCard($card);

            $order->setPaymentSource($paymentSource);
        }

        return $order;
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

    /**
     * @return ThreeDSecureResultChecker
     */
    private function getService()
    {
        return new ThreeDSecureResultChecker();
    }
}
