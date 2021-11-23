<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone\PhoneNumber as PayPalPhoneNumber;

class PhoneNumberBuilder
{
    /**
     * Default region used when parsing phone numbers. Set to germany, since the
     * PayPal PUI feature is only available there for now.
     */
    const DEFAULT_REGION = 'DE';
    const DEFAULT_COUNTRY_CODE = '49';

    /**
     * @var PhoneNumberUtil
     */
    private $phoneNumberUtil;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @param LoggerServiceInterface $logger
     */
    public function __construct($logger)
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
        $this->logger = $logger;
    }

    /**
     * @param string|null $input
     * @param string|null $defaultRegion ISO country code
     *
     * @return PayPalPhoneNumber
     */
    public function build($input, $defaultRegion = self::DEFAULT_REGION)
    {
        $phoneNumber = new PayPalPhoneNumber();

        if (empty($input)) {
            return $phoneNumber;
        }

        try {
            $output = $this->phoneNumberUtil->parse($input, $defaultRegion);

            $phoneNumber->setCountryCode(sprintf('%d', $output->getCountryCode()));
            $phoneNumber->setNationalNumber($output->getNationalNumber() ?: '');
        } catch (NumberParseException $e) {
            /*
             * In case of an error, we'll just transmit the raw phone number to
             * PayPal. If they're unable to parse it too, we'll see it in the
             * response.
             */
            $this->logger->error($e->getMessage(), $e->getTrace());

            $phoneNumber->setCountryCode(self::DEFAULT_COUNTRY_CODE);
            $phoneNumber->setNationalNumber($input);
        }

        return $phoneNumber;
    }
}
