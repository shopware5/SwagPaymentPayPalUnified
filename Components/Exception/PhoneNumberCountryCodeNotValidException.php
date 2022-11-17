<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Exception;

class PhoneNumberCountryCodeNotValidException extends PuiValidationException
{
    /**
     * @param string $countryCode
     */
    public function __construct($countryCode)
    {
        $message = \sprintf('Expect phone number country code to be 49. Got %s', $countryCode);

        parent::__construct($message);
    }
}
