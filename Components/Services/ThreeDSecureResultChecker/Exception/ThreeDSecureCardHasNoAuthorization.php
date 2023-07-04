<?php

/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception;

use Exception;

class ThreeDSecureCardHasNoAuthorization extends Exception
{
    /**
     * @param int $code
     */
    public function __construct($code)
    {
        $message = 'Card has no 3D authentication system.';

        parent::__construct($message, $code);
    }
}
