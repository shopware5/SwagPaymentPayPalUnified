<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\OnboardingStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\MerchantIntegrationsResource;

class OnboardingStatusServiceTest extends TestCase
{
    const DEFAULT_RESPONSE = [
        'capabilities' => [
            [
                'name' => 'PAY_UPON_INVOICE',
                'status' => 'ACTIVE',
                'limits' => [
                    [
                        'type' => 'GENERAL',
                    ],
                ],
            ], [
                'name' => 'CUSTOM_CARD_PROCESSING',
                'status' => 'ACTIVE',
                'limits' => [
                    [
                        'type' => 'GENERAL',
                    ],
                ],
            ],
        ],
    ];

    /**
     * @dataProvider isCapableTestDataProvider
     *
     * @param bool               $hasResponse
     * @param bool               $isCapable
     * @param bool               $hasLimits
     * @param string             $targetCapability
     * @param array<string,bool> $expectedResult
     *
     * @return void
     */
    public function testIsCapable($hasResponse, $isCapable, $hasLimits, $targetCapability, $expectedResult)
    {
        $response = $this->createResponse($hasResponse, $isCapable, $hasLimits);

        $merchantIntegrationsResourceMock = $this->createMerchantIntegrationsResourceMock($response);
        $onboardingStatusService = $this->createOnboardingStatusService($merchantIntegrationsResourceMock);

        $result = $onboardingStatusService->getIsCapableResult('fooBar', 1, false, $targetCapability);

        static::assertSame($expectedResult['isCapable'], $result->isCapable());
        static::assertSame($expectedResult['hasLimits'], $result->hasLimits());
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function isCapableTestDataProvider()
    {
        yield 'isCapable result with target capability CAPABILITY_PAY_UPON_INVOICE should be false because there is no response' => [
            false,
            false,
            false,
            OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE,
            [
                'isCapable' => false,
                'hasLimits' => false,
            ],
        ];

        yield 'isCapable result with target capability CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD should be false because there is no response' => [
            false,
            false,
            false,
            OnboardingStatusService::CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD,
            [
                'isCapable' => false,
                'hasLimits' => false,
            ],
        ];

        yield 'isCapable result with target capability CAPABILITY_PAY_UPON_INVOICE should be false because is not capable' => [
            true,
            false,
            false,
            OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE,
            [
                'isCapable' => false,
                'hasLimits' => false,
            ],
        ];

        yield 'isCapable result with target capability CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD should be false because is not capable' => [
            true,
            false,
            false,
            OnboardingStatusService::CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD,
            [
                'isCapable' => false,
                'hasLimits' => false,
            ],
        ];

        yield 'isCapable result with target capability CAPABILITY_PAY_UPON_INVOICE should be true and has limits' => [
            true,
            true,
            true,
            OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE,
            [
                'isCapable' => true,
                'hasLimits' => true,
            ],
        ];

        yield 'isCapable result with target capability CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD should be true and has limits' => [
            true,
            true,
            true,
            OnboardingStatusService::CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD,
            [
                'isCapable' => true,
                'hasLimits' => true,
            ],
        ];

        yield 'isCapable result with target capability CAPABILITY_PAY_UPON_INVOICE should be true and has no limits' => [
            true,
            true,
            false,
            OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE,
            [
                'isCapable' => true,
                'hasLimits' => false,
            ],
        ];

        yield 'isCapable result with target capability CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD should be true and has no limits' => [
            true,
            true,
            false,
            OnboardingStatusService::CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD,
            [
                'isCapable' => true,
                'hasLimits' => false,
            ],
        ];
    }

    /**
     * @param array<string,mixed>|null $response
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|MerchantIntegrationsResource
     */
    private function createMerchantIntegrationsResourceMock(array $response = null)
    {
        $merchantIntegrationsResourceMock = $this->createMock(MerchantIntegrationsResource::class);
        $merchantIntegrationsResourceMock->method('getMerchantIntegrations')->willReturn($response);

        return $merchantIntegrationsResourceMock;
    }

    /**
     * @param bool $hasResponse
     * @param bool $isCapable
     * @param bool $hasLimits
     *
     * @return array<string,mixed>|null
     */
    private function createResponse($hasResponse, $isCapable, $hasLimits)
    {
        if (!$hasResponse) {
            return null;
        }

        $response = self::DEFAULT_RESPONSE;

        if (!$isCapable) {
            foreach ($response['capabilities'] as &$capability) {
                $capability['status'] = 'NOT_ACTIVE';
            }
        }

        if (!$hasLimits) {
            foreach ($response['capabilities'] as &$capability) {
                $capability['limits'] = null;
            }
        }

        return $response;
    }

    /**
     * @return OnboardingStatusService
     */
    private function createOnboardingStatusService(MerchantIntegrationsResource $merchantIntegrationsResourceMock)
    {
        return new OnboardingStatusService($merchantIntegrationsResourceMock);
    }
}
