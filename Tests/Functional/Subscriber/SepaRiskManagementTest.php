<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Event_EventArgs;
use Enlight_Hook_HookArgs;
use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Subscriber\SepaRiskManagement;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class SepaRiskManagementTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use ContainerTrait;

    /**
     * @after
     *
     * @return void
     */
    public function resetContainer()
    {
        $this->getContainer()->reset('shopware_storefront.context_service');
        $this->getContainer()->reset('paypal_unified.subscriber.sepa_risk_management');
    }

    /**
     * @dataProvider afterRiskManagementTestDataProvider
     *
     * @param bool                     $expectedReturnValue
     * @param array<string,mixed>|null $generalSettings
     * @param bool                     $useUsCurrency
     *
     * @return void
     */
    public function testAfterRiskManagement(Enlight_Hook_HookArgs $args, $expectedReturnValue, array $generalSettings = null, $useUsCurrency = false)
    {
        if (\is_array($generalSettings)) {
            $this->insertGeneralSettingsFromArray($generalSettings);
        }

        $subscriber = $this->createSepaRiskManagementSubscriber($useUsCurrency);

        static::assertSame($expectedReturnValue, $subscriber->afterRiskManagement($args));
    }

    /**
     * @return Generator<array<mixed,mixed>>
     */
    public function afterRiskManagementTestDataProvider()
    {
        yield 'AfterRiskManagement should return TRUE because args->getReturn() is true' => [
            $this->createEnlightHookArgs(),
            true,
        ];

        yield 'AfterRiskManagement should return FALSE because paymentID does not match' => [
            $this->createEnlightHookArgs(false),
            false,
        ];

        yield 'AfterRiskManagement should return TRUE because there are no generalSettings' => [
            $this->createEnlightHookArgs(false, true),
            true,
        ];

        yield 'AfterRiskManagement should return TRUE because paypal is not active' => [
            $this->createEnlightHookArgs(false, true),
            true,
            [
                'active' => false,
                'shopId' => 1,
            ],
        ];

        yield 'AfterRiskManagement should return TRUE because country is not DE' => [
            $this->createEnlightHookArgs(false, true, null, ['additional' => ['country' => ['countryiso' => 'US']]]),
            true,
            [
                'active' => true,
                'shopId' => 1,
            ],
        ];

        yield 'AfterRiskManagement should return TRUE because the currency is not EUR' => [
            $this->createEnlightHookArgs(false, true, null, ['additional' => ['country' => ['countryiso' => 'DE']]]),
            true,
            [
                'active' => true,
                'shopId' => 1,
            ],
            true,
        ];

        yield 'AfterRiskManagement should return FALSE because all is OK' => [
            $this->createEnlightHookArgs(false, true, null, ['additional' => ['country' => ['countryiso' => 'DE']]]),
            false,
            [
                'active' => true,
                'shopId' => 1,
            ],
        ];
    }

    /**
     * @dataProvider onExecuteSepaRuleTestDataProvider
     *
     * @param bool $expectedResult
     * @param bool $useUsCurrency
     *
     * @return void
     */
    public function testOnExecuteSepaRule(Enlight_Event_EventArgs $args, $expectedResult, $useUsCurrency = false)
    {
        $subscriber = $this->createSepaRiskManagementSubscriber($useUsCurrency);

        static::assertSame($expectedResult, $subscriber->onExecuteSepaRule($args));
    }

    /**
     * @return Generator<array<mixed,mixed>>
     */
    public function onExecuteSepaRuleTestDataProvider()
    {
        yield 'OnExecuteSepaRule should return FALSE because paymentID is not set' => [
            $this->createEnlightHookArgs(),
            false,
        ];

        yield 'OnExecuteSepaRule should return TRUE because country is not DE' => [
            $this->createEnlightHookArgs(false, true, null, ['additional' => ['country' => ['countryiso' => 'US']]]),
            true,
        ];

        yield 'OnExecuteSepaRule should return TRUE because currency is not EUR' => [
            $this->createEnlightHookArgs(false, true, null, ['additional' => ['country' => ['countryiso' => 'DE']]]),
            true,
            true,
        ];

        yield 'OnExecuteSepaRule should return TRUE because currency is not EUR and country is not DE' => [
            $this->createEnlightHookArgs(false, true, null, ['additional' => ['country' => ['countryiso' => 'US']]]),
            true,
            true,
        ];

        yield 'OnExecuteSepaRule should return FALSE because paymentID is set, currency is EUR and country is DE' => [
            $this->createEnlightHookArgs(false, true, null, ['additional' => ['country' => ['countryiso' => 'DE']]]),
            false,
        ];
    }

    /**
     * @param bool $useUsCurrency
     *
     * @return SepaRiskManagement
     */
    private function createSepaRiskManagementSubscriber($useUsCurrency)
    {
        $this->getContainer()->set('shopware_storefront.context_service', $this->createContextServiceMockWithUsCurrency($useUsCurrency));

        $sepaRiskManagementSubscriber = new SepaRiskManagement(
            $this->getContainer()->get('paypal_unified.payment_method_provider'),
            $this->getContainer()->get('paypal_unified.dependency_provider'),
            $this->getContainer()->get('validator'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('paypal_unified.settings_service')
        );

        $this->getContainer()->set('paypal_unified.subscriber.sepa_risk_management', $sepaRiskManagementSubscriber);

        return $sepaRiskManagementSubscriber;
    }

    /**
     * @param bool|null                $return
     * @param bool                     $addPaymentId
     * @param array<string,mixed>|null $basket
     * @param array<string,mixed>|null $user
     *
     * @return Enlight_Hook_HookArgs
     */
    private function createEnlightHookArgs($return = true, $addPaymentId = false, array $basket = null, array $user = null)
    {
        $paymentId = 99999;

        if ($addPaymentId) {
            $paymentId = $this->getContainer()->get('paypal_unified.payment_method_provider')->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME);
        }

        $hookArgsMock = $this->createMock(Enlight_Hook_HookArgs::class);
        $hookArgsMock->method('getSubject')->willReturn($this->getContainer()->get('modules')->getModule('sAdmin'));
        $hookArgsMock->method('getReturn')->willReturn($return);
        $hookArgsMock->method('get')->willReturnMap([
            ['paymentID', $paymentId],
            ['basket', $basket],
            ['user', $user],
            ['user', $paymentId],
        ]);

        return $hookArgsMock;
    }

    /**
     * @param bool $useUsCurrency
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|ContextService
     */
    private function createContextServiceMockWithUsCurrency($useUsCurrency)
    {
        $shopContext = $this->getContainer()->get('shopware_storefront.context_service')->createShopContext(1, $useUsCurrency ? 2 : 1);

        $contextServiceMock = $this->createMock(ContextService::class);
        $contextServiceMock->method('getShopContext')->willReturn($shopContext);

        return $contextServiceMock;
    }
}
