<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;

use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class ThreeDSecure extends PayPalApiStruct
{
    const ENROLLMENT_STATUS_Y = 'Y';
    const ENROLLMENT_STATUS_N = 'N';
    const ENROLLMENT_STATUS_U = 'U';
    const ENROLLMENT_STATUS_B = 'B';

    const AUTHENTICATION_STATUS_Y = 'Y';
    const AUTHENTICATION_STATUS_N = 'N';
    const AUTHENTICATION_STATUS_R = 'R';
    const AUTHENTICATION_STATUS_A = 'A';
    const AUTHENTICATION_STATUS_U = 'U';
    const AUTHENTICATION_STATUS_C = 'C';
    const AUTHENTICATION_STATUS_I = 'I';
    const AUTHENTICATION_STATUS_D = 'D';

    /**
     * @var ThreeDSecure::ENROLLMENT_STATUS_*
     */
    private $enrollmentStatus;

    /**
     * @var ThreeDSecure::AUTHENTICATION_STATUS_*
     */
    private $authenticationStatus;

    /**
     * @return ThreeDSecure::ENROLLMENT_STATUS_*
     */
    public function getEnrollmentStatus()
    {
        return $this->enrollmentStatus;
    }

    /**
     * @param ThreeDSecure::ENROLLMENT_STATUS_* $enrollmentStatus
     *
     * @return void
     */
    public function setEnrollmentStatus($enrollmentStatus)
    {
        $this->enrollmentStatus = $enrollmentStatus;
    }

    /**
     * @return ThreeDSecure::AUTHENTICATION_STATUS_*
     */
    public function getAuthenticationStatus()
    {
        return $this->authenticationStatus;
    }

    /**
     * @param ThreeDSecure::AUTHENTICATION_STATUS_* $authenticationStatus
     *
     * @return void
     */
    public function setAuthenticationStatus($authenticationStatus)
    {
        $this->authenticationStatus = $authenticationStatus;
    }
}
