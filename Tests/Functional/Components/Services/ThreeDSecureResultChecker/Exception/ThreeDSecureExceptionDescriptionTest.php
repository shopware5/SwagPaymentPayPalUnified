<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\ThreeDSecureResultChecker\Exception;

use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception\ThreeDSecureExceptionDescription;

class ThreeDSecureExceptionDescriptionTest extends TestCase
{
    /**
     * @dataProvider getDescriptionByCodeTestDataProvider
     *
     * @param int    $code
     * @param string $expectedResult
     *
     * @return void
     */
    public function testGetDescriptionByCode($code, $expectedResult)
    {
        static::assertSame($expectedResult, ThreeDSecureExceptionDescription::getDescriptionByCode($code));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getDescriptionByCodeTestDataProvider()
    {
        yield 'Code 1000' => [
            ThreeDSecureExceptionDescription::STATUS_CODE_DEFAULT,
            'Undefined status result.',
        ];

        yield 'Code 1001' => [
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_N_NO,
            'Failed 3D authentication.',
        ];

        yield 'Code 1002' => [
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_R_NO,
            'Rejected 3D authentication.',
        ];

        yield 'Code 1003' => [
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_U_UNKNOWN,
            'Unable to complete 3D authentication. The 3D authentication system is not available.',
        ];

        yield 'Code 1004' => [
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_U_NO,
            'Unable to complete 3D authentication. The Liability is with the merchant.',
        ];

        yield 'Code 1005' => [
            ThreeDSecureExceptionDescription::STATUS_CODE_Y_C_UNKNOWN,
            'Challenge required for 3D authentication. The 3D authentication system is not available.',
        ];

        yield 'Code 1006' => [
            ThreeDSecureExceptionDescription::STATUS_CODE_Y__NO,
            'Liability is with the merchant.',
        ];

        yield 'Code 1007' => [
            ThreeDSecureExceptionDescription::STATUS_CODE_U__UNKNOWN,
            'System is unavailable at the time of the request. The 3D authentication system is not available.',
        ];

        yield 'Code 1008' => [
            ThreeDSecureExceptionDescription::STATUS_CODE___UNKNOWN,
            'The 3D authentication system is not available.',
        ];

        yield 'Any other code' => [
            9999,
            'Code not Available.',
        ];
    }
}
