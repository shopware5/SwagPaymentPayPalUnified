<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Backend;

require_once __DIR__ . '/../../../../Controllers/Backend/PaypalUnifiedSettings.php';

use Enlight_Controller_Request_RequestTestCase as RequestMock;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopware\Components\HttpClient\RequestException;
use Shopware_Controllers_Backend_PaypalUnifiedSettings;
use SwagPaymentPayPalUnified\Components\Services\Onboarding\IsCapableResult;
use SwagPaymentPayPalUnified\Components\Services\OnboardingStatusService;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;

class PaypalUnifiedSettingsTest extends TestCase
{
    use ContainerTrait;

    /**
     * @dataProvider isCapableActionTestDataProvider
     *
     * @param bool                $willThrowException
     * @param array<string,mixed> $expectedResult
     *
     * @return void
     */
    public function testIsCapableAction(RequestMock $requestMock, IsCapableResult $onboardingStatusResult, $willThrowException, array $expectedResult)
    {
        $controller = $this->createController($requestMock);
        $controller->preDispatch();

        $onboardingStatusServiceMock = $this->createMock(OnboardingStatusService::class);
        $onboardingStatusServiceMockMethod = $onboardingStatusServiceMock->method('getIsCapableResult');
        if ($willThrowException) {
            $onboardingStatusServiceMockMethod->will(static::throwException(new RequestException('fooBar', 0, null, 'fooBar')));
        } else {
            $onboardingStatusServiceMockMethod->willReturn($onboardingStatusResult);
        }

        $reflectionPropertyOnboardingStatusService = (new ReflectionClass(Shopware_Controllers_Backend_PaypalUnifiedSettings::class))->getProperty('onboardingStatusService');
        $reflectionPropertyOnboardingStatusService->setAccessible(true);
        $reflectionPropertyOnboardingStatusService->setValue($controller, $onboardingStatusServiceMock);

        $controller->isCapableAction();

        static::assertSame($expectedResult, $controller->View()->getAssign());
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function isCapableActionTestDataProvider()
    {
        yield 'ShopId is not set' => [
            $this->createRequestMock(),
            new IsCapableResult(false),
            false,
            [
                'success' => false,
                'message' => 'The parameter "shopId" is required.',
            ],
        ];

        yield 'PayerId is not set' => [
            $this->createRequestMock(1),
            new IsCapableResult(false),
            false,
            [
                'success' => false,
                'message' => 'The parameter "payerId" is required.',
            ],
        ];

        yield 'PaymentMethodCapabilityNames is not set' => [
            $this->createRequestMock(1, 'fooBar'),
            new IsCapableResult(false),
            false,
            [
                'success' => false,
                'message' => 'The parameter "paymentMethodCapabilityNames" should be a array.',
            ],
        ];

        yield 'ProductSubscriptionNames is not set' => [
            $this->createRequestMock(1, 'fooBar', []),
            new IsCapableResult(false),
            false,
            [
                'success' => false,
                'message' => 'The parameter "productSubscriptionNames" should be a array.',
            ],
        ];

        yield 'OnboardingStatusService will throw exception' => [
            $this->createRequestMock(1, 'fooBar', [OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE], []),
            new IsCapableResult(false),
            true,
            [
                'success' => false,
                'message' => 'fooBar',
                'body' => 'fooBar',
            ],
        ];

        yield 'CAPABILITY_PAY_UPON_INVOICE success true without limits' => [
            $this->createRequestMock(1, 'fooBar', [OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE], []),
            new IsCapableResult(true),
            false,
            [
                'PAY_UPON_INVOICE' => true,
                'PAY_UPON_INVOICE_HAS_LIMITS' => false,
                'success' => true,
            ],
        ];

        yield 'CAPABILITY_PAY_UPON_INVOICE and CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD success true without limits' => [
            $this->createRequestMock(1, 'fooBar', [OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE, OnboardingStatusService::CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD], []),
            new IsCapableResult(true),
            false,
            [
                'PAY_UPON_INVOICE' => true,
                'PAY_UPON_INVOICE_HAS_LIMITS' => false,
                'CUSTOM_CARD_PROCESSING' => true,
                'CUSTOM_CARD_PROCESSING_HAS_LIMITS' => false,
                'success' => true,
            ],
        ];

        yield 'CAPABILITY_PAY_UPON_INVOICE success true and limits' => [
            $this->createRequestMock(1, 'fooBar', [OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE], []),
            new IsCapableResult(true, []),
            false,
            [
                'PAY_UPON_INVOICE' => true,
                'PAY_UPON_INVOICE_HAS_LIMITS' => true,
                'success' => true,
            ],
        ];

        yield 'CAPABILITY_PAY_UPON_INVOICE and CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD success true and limits' => [
            $this->createRequestMock(1, 'fooBar', [OnboardingStatusService::CAPABILITY_PAY_UPON_INVOICE, OnboardingStatusService::CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD], []),
            new IsCapableResult(true, []),
            false,
            [
                'PAY_UPON_INVOICE' => true,
                'PAY_UPON_INVOICE_HAS_LIMITS' => true,
                'CUSTOM_CARD_PROCESSING' => true,
                'CUSTOM_CARD_PROCESSING_HAS_LIMITS' => true,
                'success' => true,
            ],
        ];
    }

    /**
     * @param int|null               $shopId
     * @param string|null            $payerId
     * @param array<int,string>|null $paymentMethodCapabilityNames
     * @param array<int,string>|null $productSubscriptionNames
     *
     * @return RequestMock
     */
    private function createRequestMock($shopId = null, $payerId = null, array $paymentMethodCapabilityNames = null, array $productSubscriptionNames = null)
    {
        $request = new RequestMock();
        $request->setParams([
            'shopId' => $shopId,
            'payerId' => $payerId,
            'paymentMethodCapabilityNames' => $paymentMethodCapabilityNames,
            'productSubscriptionNames' => $productSubscriptionNames,
            'sandbox' => true,
        ]);

        return $request;
    }

    /**
     * @return Shopware_Controllers_Backend_PaypalUnifiedSettings
     */
    private function createController(RequestMock $requestMock)
    {
        $response = new \Enlight_Controller_Response_ResponseTestCase();
        /** @var Shopware_Controllers_Backend_PaypalUnifiedSettings $controller */
        $controller = \Enlight_Class::Instance(Shopware_Controllers_Backend_PaypalUnifiedSettings::class, [$requestMock, $response]);

        $controller->setRequest($requestMock);
        $controller->setContainer($this->getContainer());
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));

        return $controller;
    }
}
