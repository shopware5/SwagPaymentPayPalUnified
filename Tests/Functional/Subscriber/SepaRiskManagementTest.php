<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Components_Session_Namespace;
use Enlight_Event_EventArgs;
use Enlight_Hook_HookArgs;
use Generator;
use PHPUnit\Framework\TestCase;
use sAdmin;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
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

        yield 'AfterRiskManagement should return TRUE because PayPal is not active' => [
            $this->createEnlightHookArgs(false, true),
            true,
            [
                'active' => false,
                'shopId' => 1,
            ],
        ];

        yield 'AfterRiskManagement should return FALSE because all is OK' => [
            $this->createEnlightHookArgs(false, true),
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
        return new SepaRiskManagement(
            $this->getContainer()->get('paypal_unified.payment_method_provider'),
            $this->createDependencyProviderMock(),
            $this->getContainer()->get('validator'),
            $this->createContextServiceMock($useUsCurrency),
            $this->getContainer()->get('paypal_unified.settings_service')
        );
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
        $adminModuleMock = $this->createMock(sAdmin::class);
        $adminModuleMock->method('executeRiskRule')->willReturn(false);
        $hookArgsMock->method('getSubject')->willReturn($adminModuleMock);
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
     * @return ContextService
     */
    private function createContextServiceMock($useUsCurrency)
    {
        $shopContext = $this->getContainer()->get('shopware_storefront.context_service')->createShopContext(1, $useUsCurrency ? 2 : 1);

        $contextServiceMock = $this->createMock(ContextService::class);
        $contextServiceMock->method('getShopContext')->willReturn($shopContext);

        return $contextServiceMock;
    }

    /**
     * @return DependencyProvider
     */
    private function createDependencyProviderMock()
    {
        $dependencyProvider = $this->createMock(DependencyProvider::class);
        $session = $this->createMock(Enlight_Components_Session_Namespace::class);
        $dependencyProvider->method('getSession')->willReturn($session);

        return $dependencyProvider;
    }
}
