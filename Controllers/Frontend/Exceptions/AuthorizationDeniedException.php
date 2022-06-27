<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions;

use SwagPaymentPayPalUnified\Components\ErrorCodes;

class AuthorizationDeniedException extends UnexpectedStatusException
{
    public function __construct()
    {
        parent::__construct('The status for authorization is DENIED', ErrorCodes::AUTHORIZATION_DENIED);
    }
}
