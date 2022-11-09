<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Exception;

use Exception;
use RuntimeException;

class OrderNotFoundException extends RuntimeException
{
    /**
     * @param string $parameter
     * @param string $value
     * @param int    $code
     */
    public function __construct($parameter, $value, $code = 0, Exception $previous = null)
    {
        $message = \sprintf('Could not find order with search parameter "%s" and value "%s"', $parameter, $value);
        parent::__construct($message, $code, $previous);
    }
}
