<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Exception;

class BirthdateNotValidException extends PuiValidationException
{
    /**
     * @param string $birthdateString
     */
    public function __construct($birthdateString)
    {
        $message = \sprintf('Order does not contain a valid birthdate. Got %s', $birthdateString);

        parent::__construct($message);
    }
}
