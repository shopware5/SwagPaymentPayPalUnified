<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker;

class ThreeDSecureResultStatus
{
    /**
     * @var string|null
     */
    private $enrollmentStatus;

    /**
     * @var string|null
     */
    private $authenticationStatus;

    /**
     * @var string|null
     */
    private $liabilityShift;

    /**
     * @param string|null $enrollmentStatus
     * @param string|null $authenticationStatus
     * @param string|null $liabilityShift
     */
    public function __construct($enrollmentStatus, $authenticationStatus, $liabilityShift)
    {
        $this->enrollmentStatus = $enrollmentStatus;
        $this->authenticationStatus = $authenticationStatus;
        $this->liabilityShift = $liabilityShift;
    }

    /**
     * @return string|null
     */
    public function getEnrollmentStatus()
    {
        return $this->enrollmentStatus;
    }

    /**
     * @return string|null
     */
    public function getAuthenticationStatus()
    {
        return $this->authenticationStatus;
    }

    /**
     * @return string|null
     */
    public function getLiabilityShift()
    {
        return $this->liabilityShift;
    }
}
