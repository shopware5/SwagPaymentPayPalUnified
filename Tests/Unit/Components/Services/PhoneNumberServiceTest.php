<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Services;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone\PhoneNumber;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;

class PhoneNumberServiceTest extends TestCase
{
    use ContainerTrait;

    /**
     * @dataProvider phoneNumberProvider
     *
     * @param string $input
     * @param string $expectedNationalNumber
     *
     * @return void
     */
    public function testBuild($input, $expectedNationalNumber)
    {
        $subject = $this->getContainer()->get('paypal_unified.phone_number_service');

        $result = $subject->buildPayPalPhoneNumber($input);

        static::assertInstanceOf(PhoneNumber::class, $result);
        static::assertSame($expectedNationalNumber, $result->getNationalNumber());
    }

    /**
     * @return array<int,array<int,string>>
     */
    public function phoneNumberProvider()
    {
        return [
            ['+1 69 427 1593', '694271593'],
            ['+49 545159211', '545159211'],
            ['732670951', '732670951'],
            ['278872439', '278872439'],
        ];
    }
}
