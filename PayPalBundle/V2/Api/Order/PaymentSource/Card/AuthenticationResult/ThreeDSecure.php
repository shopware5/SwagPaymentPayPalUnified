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
    const AUTHENTICATION_STATUS_Y = 'Y';
    const AUTHENTICATION_STATUS_N = 'N';

    /**
     * @var string
     * @phpstan-var ThreeDSecure::ENROLLMENT_STATUS_*
     */
    private $enrollmentStatus;

    /**
     * @var string
     * @phpstan-var ThreeDSecure::AUTHENTICATION_STATUS_*
     */
    private $authenticationStatus;

    /**
     * @return string
     * @phpstan-return ThreeDSecure::ENROLLMENT_STATUS_*
     */
    public function getEnrollmentStatus()
    {
        return $this->enrollmentStatus;
    }

    /**
     * @param string $enrollmentStatus
     * @phpstan-param ThreeDSecure::ENROLLMENT_STATUS_* $enrollmentStatus
     */
    public function setEnrollmentStatus($enrollmentStatus)
    {
        $this->enrollmentStatus = $enrollmentStatus;
    }

    /**
     * @return string
     * @phpstan-return ThreeDSecure::AUTHENTICATION_STATUS_*
     */
    public function getAuthenticationStatus()
    {
        return $this->authenticationStatus;
    }

    /**
     * @param string $authenticationStatus
     * @phpstan-param ThreeDSecure::AUTHENTICATION_STATUS_* $authenticationStatus
     */
    public function setAuthenticationStatus($authenticationStatus)
    {
        $this->authenticationStatus = $authenticationStatus;
    }
}
