<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions;

use Exception;
use SwagPaymentPayPalUnified\Components\ErrorCodes;

class PendingException extends Exception
{
    const TYPE_AUTHORIZATION = 'authorization';
    const TYPE_CAPTURE = 'capture';

    /**
     * @param self::TYPE_* $type
     */
    public function __construct($type)
    {
        $code = $type === self::TYPE_CAPTURE ? ErrorCodes::CAPTURE_PENDING : ErrorCodes::AUTHORIZATION_PENDING;

        parent::__construct(\sprintf('The status for %s is PENDNG', $type), $code);
    }
}
