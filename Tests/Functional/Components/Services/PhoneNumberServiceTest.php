<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use Generator;
use PDO;
use PHPUnit\Framework\TestCase;
use Shopware_Components_Config;
use SwagPaymentPayPalUnified\Components\Services\LoggerService;
use SwagPaymentPayPalUnified\Components\Services\PhoneNumberService;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone\PhoneNumber as PayPalPhoneNumber;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class PhoneNumberServiceTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;

    /**
     * @dataProvider buildPayPalPhoneNumberTestDataProvider
     *
     * @param string|null $rawInput
     *
     * @return void
     */
    public function testBuildPayPalPhoneNumber($rawInput, PayPalPhoneNumber $expectedResult)
    {
        $phoneNumberService = $this->getContainer()->get('paypal_unified.phone_number_service');

        $result = $phoneNumberService->buildPayPalPhoneNumber($rawInput);

        static::assertSame($expectedResult->getNationalNumber(), $result->getNationalNumber());
        static::assertSame($expectedResult->getCountryCode(), $result->getCountryCode());
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function buildPayPalPhoneNumberTestDataProvider()
    {
        yield 'Raw input is NULL' => [
            null,
            new PayPalPhoneNumber(),
        ];

        yield 'Raw input is foo9939bar' => [
            'foo9939bar',
            (new PayPalPhoneNumber())->assign(['nationalNumber' => '9939227', 'countryCode' => '49']),
        ];

        yield 'Raw input is 08154711' => [
            '08154711',
            (new PayPalPhoneNumber())->assign(['nationalNumber' => '8154711', 'countryCode' => '49']),
        ];

        yield 'Raw input is 02555928850' => [
            '02555928850',
            (new PayPalPhoneNumber())->assign(['nationalNumber' => '2555928850', 'countryCode' => '49']),
        ];

        yield 'Raw input is +49 (0) 2555 92885-0' => [
            '+49 (0) 2555 92885-0',
            (new PayPalPhoneNumber())->assign(['nationalNumber' => '2555928850', 'countryCode' => '49']),
        ];
    }

    /**
     * @dataProvider getValidPhoneNumberStringTestDataProvider
     *
     * @param string|null $rawInput
     * @param string|null $expectedResult
     *
     * @return void
     */
    public function testGetValidPhoneNumberString($rawInput, $expectedResult)
    {
        $phoneNumberService = $this->getContainer()->get('paypal_unified.phone_number_service');

        $result = $phoneNumberService->getValidPhoneNumberString((string) $rawInput);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getValidPhoneNumberStringTestDataProvider()
    {
        yield 'Raw input is NULL Region is NULL' => [
            null,
            null,
        ];

        yield 'Raw input is foo9939bar Region is NULL' => [
            'foo9939bar',
            '09939227',
        ];

        yield 'Raw input is 08154711 Region is NULL' => [
            '08154711',
            '08154711',
        ];

        yield 'Raw input is 02555928850 Region is NULL' => [
            '02555928850',
            '02555928850',
        ];

        yield 'Raw input is +49 (0) 2555 92885-0 Region is NULL' => [
            '+49 (0) 2555 92885-0',
            '02555928850',
        ];
    }

    /**
     * @dataProvider savePhoneNumberTestDataProvider
     *
     * @param int         $addressId
     * @param string|null $phoneNumber
     * @param bool        $showPhoneNumberFieldConfigResult
     * @param string|null $expectedResult
     *
     * @return void
     */
    public function testSavePhoneNumber($addressId, $phoneNumber, $showPhoneNumberFieldConfigResult, $expectedResult)
    {
        $this->setPhoneNumberToNull($addressId);
        static::assertNull($this->getPhoneNumber($addressId));

        $config = $this->createMock(Shopware_Components_Config::class);
        $config->method('get')->willReturn($showPhoneNumberFieldConfigResult);

        $connection = $this->getContainer()->get('dbal_connection');

        $phoneNumberService = new PhoneNumberService(
            $this->createMock(LoggerService::class),
            $connection,
            $config
        );

        $phoneNumberService->savePhoneNumber($addressId, $phoneNumber);

        $result = $this->getPhoneNumber($addressId);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function savePhoneNumberTestDataProvider()
    {
        yield 'should not save phone number because showphonenumberfield is false' => [
            1,
            '123456789',
            false,
            null,
        ];

        yield 'should not save phone number because phone number is null' => [
            1,
            null,
            true,
            null,
        ];

        yield 'should save phone number' => [
            1,
            '123456789',
            true,
            '123456789',
        ];
    }

    /**
     * @param int $addressId
     *
     * @return void
     */
    private function setPhoneNumberToNull($addressId)
    {
        $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->update('s_user_addresses')
            ->set('phone', ':phoneNumber')
            ->where('id = :addressId')
            ->setParameter('phoneNumber', null)
            ->setParameter('addressId', $addressId)
            ->execute();
    }

    /**
     * @param int $addressId
     *
     * @return string|null
     */
    private function getPhoneNumber($addressId)
    {
        $result = $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->select(['phone'])
            ->from('s_user_addresses')
            ->where('id = :addressId')
            ->setParameter('addressId', $addressId)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);

        if (\is_string($result)) {
            return $result;
        }

        return null;
    }
}
