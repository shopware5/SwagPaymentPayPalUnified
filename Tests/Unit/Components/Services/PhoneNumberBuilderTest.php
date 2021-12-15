<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Services;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\PhoneNumberBuilder;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone\PhoneNumber;

class PhoneNumberBuilderTest extends TestCase
{
    /**
     * @dataProvider phoneNumberProvider
     *
     * @param string $input
     * @param string $defaultRegion
     * @param string $expectedCountryCode
     * @param string $expectedNationalNumber
     *
     * @return void
     */
    public function testBuild($input, $defaultRegion, $expectedCountryCode, $expectedNationalNumber)
    {
        $subject = new PhoneNumberBuilder($this->getLogger());

        if ($defaultRegion) {
            $result = $subject->build($input, $defaultRegion);
        } else {
            $result = $subject->build($input);
        }

        static::assertInstanceOf(PhoneNumber::class, $result);
        static::assertSame($expectedCountryCode, $result->getCountryCode());
        static::assertSame($expectedNationalNumber, $result->getNationalNumber());
    }

    public function phoneNumberProvider()
    {
        return [
            ['+1 69 427 1593', null, '1', '694271593'],
            ['+49 545159211', null, PhoneNumberBuilder::DEFAULT_COUNTRY_CODE, '545159211'],
            ['732670951', null, PhoneNumberBuilder::DEFAULT_COUNTRY_CODE, '732670951'],
            ['278872439', 'GB', '44', '278872439'],
        ];
    }

    protected function getLogger()
    {
        $logger = static::createMock(LoggerServiceInterface::class);

        // Assert there are no exceptions
        $logger->expects(static::never())
            ->method('error');

        return $logger;
    }
}
