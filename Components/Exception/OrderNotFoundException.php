<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Exception;

class OrderNotFoundException extends \RuntimeException
{
    public function __construct($parameter, $value, $code = 0, \Throwable $previous = null)
    {
        $message = sprintf('Could not find order with search parameter "%s" and value "%s"', $parameter, $value);
        parent::__construct($message, $code, $previous);
    }
}
