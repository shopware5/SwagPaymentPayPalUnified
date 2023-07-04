<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker;

use Exception;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception\ThreeDSecureAuthorizationCanceledException;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception\ThreeDSecureAuthorizationFailedException;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception\ThreeDSecureAuthorizationRejectedException;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception\ThreeDSecureCardHasNoAuthorization;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception\ThreeDSecureExceptionDescription;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult\ThreeDSecure;

class ThreeDSecureResultChecker
{
    /**
     * List of responses: https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/response-parameters/
     *
     * @return bool
     */
    public function checkStaus(Order $payPalOrder)
    {
        $status = $this->getStatus($payPalOrder);

        /*
         * Successful responses:
         *
         * EnrollmentStatus     | Authentication_Status     | LiabilityShift
         * Y                    | Y                         | POSSIBLE
         * Y                    | A                         | POSSIBLE
         * N                    |                           | NO
         * U                    |                           | NO
         * B                    |                           | NO
         */
        if ($status->getEnrollmentStatus() === ThreeDSecure::ENROLLMENT_STATUS_Y
            && $status->getAuthenticationStatus() === ThreeDSecure::AUTHENTICATION_STATUS_Y
            && $status->getLiabilityShift() === AuthenticationResult::LIABILITY_SHIFT_POSSIBLE) {
            return true;
        }

        if ($status->getEnrollmentStatus() === ThreeDSecure::ENROLLMENT_STATUS_Y
            && $status->getAuthenticationStatus() === ThreeDSecure::AUTHENTICATION_STATUS_A
            && $status->getLiabilityShift() === AuthenticationResult::LIABILITY_SHIFT_POSSIBLE) {
            return true;
        }

        if ($status->getEnrollmentStatus() === ThreeDSecure::ENROLLMENT_STATUS_N
            && $status->getLiabilityShift() === AuthenticationResult::LIABILITY_SHIFT_NO) {
            return true;
        }

        if ($status->getEnrollmentStatus() === ThreeDSecure::ENROLLMENT_STATUS_U
            && $status->getLiabilityShift() === AuthenticationResult::LIABILITY_SHIFT_NO) {
            return true;
        }

        if ($status->getEnrollmentStatus() === ThreeDSecure::ENROLLMENT_STATUS_B
            && $status->getLiabilityShift() === AuthenticationResult::LIABILITY_SHIFT_NO) {
            return true;
        }

        /*
         * Do not continue
         *
         * EnrollmentStatus     | Authentication_Status    | LiabilityShift
         * Y                    | N                        | NO
         * Y                    | R                        | NO
         */
        if ($status->getEnrollmentStatus() === ThreeDSecure::ENROLLMENT_STATUS_Y
            && $status->getAuthenticationStatus() === ThreeDSecure::AUTHENTICATION_STATUS_N
            && $status->getLiabilityShift() === AuthenticationResult::LIABILITY_SHIFT_NO) {
            throw new ThreeDSecureAuthorizationFailedException($status, ThreeDSecureExceptionDescription::STATUS_CODE_Y_N_NO);
        }

        if ($status->getEnrollmentStatus() === ThreeDSecure::ENROLLMENT_STATUS_Y
            && $status->getAuthenticationStatus() === ThreeDSecure::AUTHENTICATION_STATUS_R
            && $status->getLiabilityShift() === AuthenticationResult::LIABILITY_SHIFT_NO) {
            throw new ThreeDSecureAuthorizationFailedException($status, ThreeDSecureExceptionDescription::STATUS_CODE_Y_R_NO);
        }

        /*
         * Request cardholder to retry.
         *
         * EnrollmentStatus    | Authentication_Status     | LiabilityShift
         * Y                   | U                         | UNKNOWN
         * Y                   | U                         | NO
         * Y                   | C                         | UNKNOWN
         * Y                   |                           | NO
         * U                   |                           | UNKNOWN
         *                     |                           | UNKNOWN
         */
        if ($status->getEnrollmentStatus() === ThreeDSecure::ENROLLMENT_STATUS_Y
            && $status->getAuthenticationStatus() === ThreeDSecure::AUTHENTICATION_STATUS_U
            && $status->getLiabilityShift() === AuthenticationResult::LIABILITY_SHIFT_UNKNOWN) {
            throw new ThreeDSecureAuthorizationRejectedException($status, ThreeDSecureExceptionDescription::STATUS_CODE_Y_U_UNKNOWN);
        }

        if ($status->getEnrollmentStatus() === ThreeDSecure::ENROLLMENT_STATUS_Y
            && $status->getAuthenticationStatus() === ThreeDSecure::AUTHENTICATION_STATUS_U
            && $status->getLiabilityShift() === AuthenticationResult::LIABILITY_SHIFT_NO) {
            throw new ThreeDSecureAuthorizationRejectedException($status, ThreeDSecureExceptionDescription::STATUS_CODE_Y_U_NO);
        }

        if ($status->getEnrollmentStatus() === ThreeDSecure::ENROLLMENT_STATUS_Y
            && $status->getAuthenticationStatus() === ThreeDSecure::AUTHENTICATION_STATUS_C
            && $status->getLiabilityShift() === AuthenticationResult::LIABILITY_SHIFT_UNKNOWN) {
            throw new ThreeDSecureAuthorizationRejectedException($status, ThreeDSecureExceptionDescription::STATUS_CODE_Y_C_UNKNOWN);
        }

        if ($status->getEnrollmentStatus() === ThreeDSecure::ENROLLMENT_STATUS_Y
            && $status->getLiabilityShift() === AuthenticationResult::LIABILITY_SHIFT_NO) {
            throw new ThreeDSecureAuthorizationRejectedException($status, ThreeDSecureExceptionDescription::STATUS_CODE_Y__NO);
        }

        if ($status->getEnrollmentStatus() === ThreeDSecure::ENROLLMENT_STATUS_U
            && $status->getLiabilityShift() === AuthenticationResult::LIABILITY_SHIFT_UNKNOWN) {
            throw new ThreeDSecureAuthorizationRejectedException($status, ThreeDSecureExceptionDescription::STATUS_CODE_U__UNKNOWN);
        }

        if ($status->getLiabilityShift() === AuthenticationResult::LIABILITY_SHIFT_UNKNOWN) {
            throw new ThreeDSecureAuthorizationCanceledException($status, ThreeDSecureExceptionDescription::STATUS_CODE___UNKNOWN);
        }

        $messageTemplate = 'ThreeDSecure: Something went wrong: EnrollmentStatus: %s, AuthenticationStatus: %s, LiabilityShift: %s';
        $message = sprintf(
            $messageTemplate,
            $status->getEnrollmentStatus(),
            $status->getAuthenticationStatus(),
            $status->getLiabilityShift()
        );

        throw new Exception($message, ThreeDSecureExceptionDescription::STATUS_CODE_DEFAULT);
    }

    /**
     * @return ThreeDSecureResultStatus
     */
    private function getStatus(Order $payPalOrder)
    {
        $threeDSecure = $this->get3DSecure($payPalOrder);

        if (!$threeDSecure instanceof ThreeDSecure) {
            throw new ThreeDSecureCardHasNoAuthorization(ThreeDSecureExceptionDescription::STATUS_CODE_NO_3DSECURE);
        }

        return new ThreeDSecureResultStatus(
            $threeDSecure->getEnrollmentStatus(),
            $threeDSecure->getAuthenticationStatus(),
            $this->getLiabilityShift($payPalOrder)
        );
    }

    /**
     * @return ThreeDSecure|null
     */
    private function get3DSecure(Order $payPalOrder)
    {
        $authenticationResult = $this->getAuthenticationResult($payPalOrder);
        if (!$authenticationResult instanceof AuthenticationResult) {
            return null;
        }

        return $authenticationResult->getThreeDSecure();
    }

    /**
     * @return AuthenticationResult::LIABILITY_SHIFT_*|null
     */
    private function getLiabilityShift(Order $payPalOrder)
    {
        $authenticationResult = $this->getAuthenticationResult($payPalOrder);

        if (!$authenticationResult instanceof AuthenticationResult) {
            return null;
        }

        return $authenticationResult->getLiabilityShift();
    }

    /**
     * @return AuthenticationResult|null
     */
    private function getAuthenticationResult(Order $payPalOrder)
    {
        $paymentSource = $payPalOrder->getPaymentSource();
        if (!$paymentSource instanceof PaymentSource) {
            return null;
        }

        $card = $paymentSource->getCard();
        if (!$card instanceof Card) {
            return null;
        }

        return $card->getAuthenticationResult();
    }
}
