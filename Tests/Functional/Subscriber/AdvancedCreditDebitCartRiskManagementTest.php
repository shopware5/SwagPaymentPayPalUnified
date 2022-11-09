<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Hook_HookArgs;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Subscriber\AdvancedCreditDebitCardRiskManagement;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class AdvancedCreditDebitCartRiskManagementTest extends TestCase
{
    use ShopRegistrationTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use ContainerTrait;

    /**
     * @dataProvider afterRiskManagementTestDataProvider
     *
     * @param array<string,mixed>|null $generalSettings
     * @param array<string,mixed>|null $acdcSetting
     * @param bool                     $expectedResult
     *
     * @return void
     */
    public function testAfterRiskManagement($generalSettings, $acdcSetting, Enlight_Hook_HookArgs $args, $expectedResult)
    {
        if ($generalSettings !== null) {
            $this->insertGeneralSettingsFromArray($generalSettings);
        }

        if ($acdcSetting !== null) {
            $this->insertAdvancedCreditDebitCardSettingsFromArray($acdcSetting);
        }

        $subscriber = $this->createAdvancedCreditDebitCardRiskManagementSubscriber();

        $result = $subscriber->afterRiskManagement($args);

        if (method_exists($this, 'assertSame')) {
            static::assertSame($expectedResult, $result);
        } else {
            static::assertEquals($expectedResult, $result);
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function afterRiskManagementTestDataProvider()
    {
        yield 'Args->getReturn is true' => [
            null,
            null,
            $this->createEnlightHookArgs(true),
            true,
        ];

        yield 'Another payment method id' => [
            null,
            null,
            $this->createEnlightHookArgs(false, 999),
            false,
        ];

        yield 'No GeneralSettings' => [
            null,
            null,
            $this->createEnlightHookArgs(),
            true,
        ];

        yield 'GeneralSettings: Paypal is not active' => [
            ['shopId' => 1, 'active' => 0],
            null,
            $this->createEnlightHookArgs(),
            true,
        ];

        yield 'No AdvancedCreditDebitCardSettings' => [
            ['shopId' => 1, 'active' => 1],
            null,
            $this->createEnlightHookArgs(),
            true,
        ];

        yield 'AdvancedCreditDebitCardSettings: Acdc is not active' => [
            ['shopId' => 1, 'active' => 1],
            ['shopId' => 1, 'active' => 0],
            $this->createEnlightHookArgs(),
            true,
        ];

        yield 'AdvancedCreditDebitCardSettings: Acdc is active' => [
            ['shopId' => 1, 'active' => 1],
            ['shopId' => 1, 'active' => 1],
            $this->createEnlightHookArgs(),
            false,
        ];
    }

    /**
     * @param bool     $return
     * @param int|null $paymentMethodId
     *
     * @return Enlight_Hook_HookArgs
     */
    private function createEnlightHookArgs($return = false, $paymentMethodId = null)
    {
        if ($paymentMethodId === null) {
            $paymentMethodId = $this->getContainer()->get('paypal_unified.payment_method_provider')->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME);
        }

        $reflectionMethod = (new ReflectionClass(Enlight_Hook_HookArgs::class))->getConstructor();
        static::assertInstanceOf(ReflectionMethod::class, $reflectionMethod);
        $numberOfParameters = $reflectionMethod->getNumberOfParameters();

        if ($numberOfParameters > 1) {
            $enlightHookHookArgs = new Enlight_Hook_HookArgs($this, 'AnyMethod', ['paymentID' => $paymentMethodId]);
        } else {
            $enlightHookHookArgs = new Enlight_Hook_HookArgs(['paymentID' => $paymentMethodId]);
        }

        $enlightHookHookArgs->setReturn($return);
        $enlightHookHookArgs->offsetSet('paymentID', $paymentMethodId);

        return $enlightHookHookArgs;
    }

    /**
     * @return AdvancedCreditDebitCardRiskManagement
     */
    private function createAdvancedCreditDebitCardRiskManagementSubscriber()
    {
        return new AdvancedCreditDebitCardRiskManagement(
            $this->getContainer()->get('paypal_unified.payment_method_provider'),
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('shopware_storefront.context_service')
        );
    }
}
