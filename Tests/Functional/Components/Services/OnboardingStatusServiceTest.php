<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\LoggerService;
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
     * @dataProvider getIsCapableResultShouldReturnValuesForPaymentsReceivableAndPrimaryEmailConfirmedTestDataProvider
     *
     * @param bool|null $paymentsReceivable
     * @param bool|null $primaryEmailConfirmed
     * @param string    $targetCapability
     * @param bool      $expectedPaymentsReceivableResult
     * @param bool      $expectedPrimaryEmailConfirmedResult
     *
     * @return void
     */
    public function testGetIsCapableResultShouldReturnValuesForPaymentsReceivableAndPrimaryEmailConfirmed(
        $paymentsReceivable,
        $primaryEmailConfirmed,
        $targetCapability,
        $expectedPaymentsReceivableResult,
        $expectedPrimaryEmailConfirmedResult
    ) {
        $response = $this->getMerchantIntegrationsResponse($paymentsReceivable, $primaryEmailConfirmed);

        $onboardingStatusService = $this->createOnboardingStatusService(
            $this->createMerchantIntegrationsResourceMock($response)
        );

        $result = $onboardingStatusService->getIsCapableResult('anyPayerId', 1, false, $targetCapability);

        static::assertSame($expectedPaymentsReceivableResult, $result->getIsPaymentsReceivable());
        static::assertSame($expectedPrimaryEmailConfirmedResult, $result->getIsPrimaryEmailConfirmed());
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getIsCapableResultShouldReturnValuesForPaymentsReceivableAndPrimaryEmailConfirmedTestDataProvider()
    {
        yield 'CAPABILITY_PAY_UPON_INVOICE paymentsReceivable is false and primaryEmailConfirmed is false' => [
            false,
            false,
            OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE,
            false,
            false,
        ];

        yield 'CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD paymentsReceivable is false and primaryEmailConfirmed is false' => [
            false,
            false,
            OnboardingStatusService::CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD,
            false,
            false,
        ];

        yield 'CAPABILITY_PAY_UPON_INVOICE paymentsReceivable is true and primaryEmailConfirmed is false' => [
            true,
            false,
            OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE,
            true,
            false,
        ];

        yield 'CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD paymentsReceivable is true and primaryEmailConfirmed is false' => [
            true,
            false,
            OnboardingStatusService::CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD,
            true,
            false,
        ];

        yield 'CAPABILITY_PAY_UPON_INVOICE paymentsReceivable is true and primaryEmailConfirmed is true' => [
            true,
            true,
            OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE,
            true,
            true,
        ];

        yield 'CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD paymentsReceivable is true and primaryEmailConfirmed is true' => [
            true,
            true,
            OnboardingStatusService::CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD,
            true,
            true,
        ];

        yield 'CAPABILITY_PAY_UPON_INVOICE paymentsReceivable is false and primaryEmailConfirmed is true' => [
            false,
            true,
            OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE,
            false,
            true,
        ];

        yield 'CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD paymentsReceivable is false and primaryEmailConfirmed is true' => [
            false,
            true,
            OnboardingStatusService::CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD,
            false,
            true,
        ];

        yield 'CAPABILITY_PAY_UPON_INVOICE paymentsReceivable is null and primaryEmailConfirmed is null' => [
            null,
            null,
            OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE,
            false,
            false,
        ];

        yield 'CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD paymentsReceivable is null and primaryEmailConfirmed is null' => [
            null,
            null,
            OnboardingStatusService::CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD,
            false,
            false,
        ];

        yield 'CAPABILITY_PAY_UPON_INVOICE paymentsReceivable is true and primaryEmailConfirmed is null' => [
            true,
            null,
            OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE,
            true,
            false,
        ];

        yield 'CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD paymentsReceivable is true and primaryEmailConfirmed is null' => [
            true,
            null,
            OnboardingStatusService::CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD,
            true,
            false,
        ];

        yield 'CAPABILITY_PAY_UPON_INVOICE paymentsReceivable is null and primaryEmailConfirmed is true' => [
            null,
            true,
            OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE,
            false,
            true,
        ];

        yield 'CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD paymentsReceivable is null and primaryEmailConfirmed is true' => [
            null,
            true,
            OnboardingStatusService::CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD,
            false,
            true,
        ];
    }

    /**
     * @param array<string,mixed>|null $response
     *
     * @return MockObject|MerchantIntegrationsResource
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
     * @param bool|null $paymentsReceivable
     * @param bool|null $primaryEmailConfirmed
     *
     * @return array<string,mixed>
     */
    private function getMerchantIntegrationsResponse($paymentsReceivable, $primaryEmailConfirmed)
    {
        $response = [
            'products' => [
                [
                    'name' => 'PAYMENT_METHODS',
                    'vetting_status' => 'SUBSCRIBED',
                    'capabilities' => [
                        'PAY_UPON_INVOICE',
                    ],
                ], [
                    'name' => 'PPCP_CUSTOM',
                    'vetting_status' => 'SUBSCRIBED',
                    'capabilities' => [
                        'CUSTOM_CARD_PROCESSING',
                    ],
                ], [
                    'name' => 'PPCP_STANDARD',
                    'vetting_status' => 'SUBSCRIBED',
                    'capabilities' => [
                        'STANDARD_CARD_PROCESSING',
                    ],
                ],
            ],
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
                ], [
                    'name' => 'STANDARD_CARD_PROCESSING',
                    'status' => 'ACTIVE',
                ],
            ],
            'payments_receivable' => $paymentsReceivable,
            'primary_email_confirmed' => $primaryEmailConfirmed,
        ];

        if ($paymentsReceivable === null) {
            unset($response['payments_receivable']);
        }

        if ($primaryEmailConfirmed === null) {
            unset($response['primary_email_confirmed']);
        }

        return $response;
    }

    /**
     * @return OnboardingStatusService
     */
    private function createOnboardingStatusService(MerchantIntegrationsResource $merchantIntegrationsResourceMock)
    {
        $logger = $this->createMock(LoggerService::class);

        return new OnboardingStatusService($merchantIntegrationsResourceMock, $logger);
    }
}
