<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions;

class AuthorizationPendingException extends PendingException
{
    public function __construct()
    {
        parent::__construct(PendingException::TYPE_AUTHORIZATION);
    }
}
