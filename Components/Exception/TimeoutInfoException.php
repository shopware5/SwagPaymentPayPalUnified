<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Exception;

use Exception;

class TimeoutInfoException extends Exception
{
    /**
     * @param string $paypalOrderId
     */
    public function __construct($paypalOrderId)
    {
        $message = \sprintf('Timeout information not found for PayPal order with ID: %s', $paypalOrderId);

        parent::__construct($message);
    }
}
