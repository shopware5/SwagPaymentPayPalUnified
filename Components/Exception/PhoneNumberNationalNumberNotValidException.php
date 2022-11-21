<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Exception;

class PhoneNumberNationalNumberNotValidException extends PuiValidationException
{
    /**
     * @param string $phoneNumber
     */
    public function __construct($phoneNumber)
    {
        $message = \sprintf('Expect phone number. Got %s', $phoneNumber);

        parent::__construct($message);
    }
}
