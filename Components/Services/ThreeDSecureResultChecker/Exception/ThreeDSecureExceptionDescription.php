<?php

/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception;

final class ThreeDSecureExceptionDescription
{
    /**
     * ENROLLMENT STATUS
     * Response | Description
     * Y        | Card type and issuing bank are ready to complete a 3D Secure authentication.
     * N        | Card type and issuing bank are not ready to complete a 3D Secure authentication.
     * U        | System is unavailable at the time of the request.
     * B        | System has bypassed authentication.
     *
     * AUTHENTICATION STATUS
     * Response | Description
     * Y        | Successful authentication.
     * N        | Failed authentication.
     * R        | Rejected authentication.
     * A        | Attempted authentication.
     * U        | Unable to complete authentication.
     * C        | Challenge required for authentication.
     * I        | Information only.
     * D        |    Decoupled authentication.
     *
     * LIABILITY SHIFT
     * Response | Description
     * POSSIBLE | Liability might shift to the card issuer.
     * NO       | Liability is with the merchant.
     * UNKNOWN  | The authentication system is not available.
     */

    /**
     * Default status code for not defined status
     */
    const STATUS_CODE_DEFAULT = 1000;

    /**
     * Failed authentication
     */
    const STATUS_CODE_Y_N_NO = 1001;

    /**
     * Rejected authentication
     */
    const STATUS_CODE_Y_R_NO = 1002;

    /**
     * Unable to complete authentication
     * The authentication system is not available
     */
    const STATUS_CODE_Y_U_UNKNOWN = 1003;

    /**
     * Unable to complete authentication
     * Liability is with the merchant
     */
    const STATUS_CODE_Y_U_NO = 1004;

    /**
     * Challenge required for authentication.
     * The authentication system is not available
     */
    const STATUS_CODE_Y_C_UNKNOWN = 1005;

    /**
     * Liability is with the merchant
     */
    const STATUS_CODE_Y__NO = 1006;

    /**
     * System is unavailable at the time of the request
     * The authentication system is not available
     */
    const STATUS_CODE_U__UNKNOWN = 1007;

    /**
     * The authentication system is not available
     */
    const STATUS_CODE___UNKNOWN = 1008;

    /**
     * The card has no authentication system (3DSecure)
     */
    const STATUS_CODE_NO_3DSECURE = 1009;

    private function __construct()
    {
    }

    /**
     * @param int $code
     *
     * @return string
     */
    public static function getDescriptionByCode($code)
    {
        switch ($code) {
            case self::STATUS_CODE_DEFAULT:
                return 'Undefined status result.';
            case self::STATUS_CODE_Y_N_NO:
                return 'Failed 3D authentication.';
            case self::STATUS_CODE_Y_R_NO:
                return 'Rejected 3D authentication.';
            case self::STATUS_CODE_Y_U_UNKNOWN:
                return 'Unable to complete 3D authentication. The 3D authentication system is not available.';
            case self::STATUS_CODE_Y_U_NO:
                return 'Unable to complete 3D authentication. The Liability is with the merchant.';
            case self::STATUS_CODE_Y_C_UNKNOWN:
                return 'Challenge required for 3D authentication. The 3D authentication system is not available.';
            case self::STATUS_CODE_Y__NO:
                return 'Liability is with the merchant.';
            case self::STATUS_CODE_U__UNKNOWN:
                return 'System is unavailable at the time of the request. The 3D authentication system is not available.';
            case self::STATUS_CODE___UNKNOWN:
                return 'The 3D authentication system is not available.';
            case self::STATUS_CODE_NO_3DSECURE:
                return 'Card has no 3D authentication system.';
            default:
                return 'Code not Available.';
        }
    }
}
