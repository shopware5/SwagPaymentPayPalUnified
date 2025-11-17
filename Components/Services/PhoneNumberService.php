<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Doctrine\DBAL\Connection;
use Exception;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Shopware_Components_Config as CoreConfig;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone\PhoneNumber as PayPalPhoneNumber;

class PhoneNumberService
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
     * @var Connection
     */
    private $connection;

    /**
     * @var CoreConfig
     */
    private $config;

    public function __construct(LoggerServiceInterface $logger, Connection $connection, CoreConfig $config)
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
        $this->logger = $logger;
        $this->connection = $connection;
        $this->config = $config;
    }

    /**
     * @param string|null $phoneNumberRawInput
     *
     * @return PayPalPhoneNumber
     */
    public function buildPayPalPhoneNumber($phoneNumberRawInput)
    {
        $phoneNumber = new PayPalPhoneNumber();

        if (empty($phoneNumberRawInput)) {
            return $phoneNumber;
        }

        try {
            $output = $this->phoneNumberUtil->parse($phoneNumberRawInput, self::DEFAULT_REGION);

            $phoneNumber->setCountryCode(\sprintf('%d', $output->getCountryCode()));
            $phoneNumber->setNationalNumber($output->getNationalNumber() ?: '');
        } catch (NumberParseException $numberParseException) {
            /*
             * In case of an error, we'll just transmit the raw phone number to
             * PayPal. If they're unable to parse it too, we'll see it in the
             * response.
             */
            $this->logger->error($numberParseException->getMessage(), $numberParseException->getTrace());

            $phoneNumber->setCountryCode(self::DEFAULT_COUNTRY_CODE);
            $phoneNumber->setNationalNumber($phoneNumberRawInput);
        }

        return $phoneNumber;
    }

    /**
     * @param string $phoneNumberRawInput
     *
     * @return string|null
     */
    public function getValidPhoneNumberString($phoneNumberRawInput)
    {
        try {
            $phoneNumber = $this->phoneNumberUtil->parse($phoneNumberRawInput, self::DEFAULT_REGION);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());

            return null;
        }

        if (!$this->phoneNumberUtil->isValidNumber($phoneNumber)) {
            return null;
        }

        $length = \strlen((string) $phoneNumber->getNationalNumber()) + $phoneNumber->getNumberOfLeadingZeros();

        return \str_pad((string) $phoneNumber->getNationalNumber(), $length, '0', \STR_PAD_LEFT);
    }

    /**
     * @param int         $addressId
     * @param string|null $phoneNumber
     *
     * @return void
     */
    public function savePhoneNumber($addressId, $phoneNumber)
    {
        $isPhoneNumberChangeable = $this->config->get('showphonenumberfield');
        if ($isPhoneNumberChangeable && $phoneNumber !== null) {
            $this->connection->createQueryBuilder()->update('s_user_addresses')
                ->set('phone', ':phoneNumber')
                ->where('id = :addressId')
                ->setParameter('phoneNumber', $phoneNumber)
                ->setParameter('addressId', $addressId)
                ->execute();
        }
    }
}
