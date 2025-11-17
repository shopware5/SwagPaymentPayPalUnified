<?php

/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception;

use Exception;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\ThreeDSecureResultStatus;

class ThreeDSecureAuthorizationRejectedException extends Exception
{
    /**
     * @param int $code
     */
    public function __construct(ThreeDSecureResultStatus $status, $code)
    {
        $messageTemplate = 'ThreeDSecure rejected with status: EnrollmentStatus: %s, AuthenticationStatus: %s, LiabilityShift: %s REASON: %s';
        $message = \sprintf(
            $messageTemplate,
            $status->getEnrollmentStatus(),
            $status->getAuthenticationStatus(),
            $status->getLiabilityShift(),
            ThreeDSecureExceptionDescription::getDescriptionByCode($code)
        );

        parent::__construct($message, $code);
    }
}
