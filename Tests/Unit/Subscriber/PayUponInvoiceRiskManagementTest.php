<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Currency;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\Models\Settings\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\Subscriber\PayUponInvoiceRiskManagement;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PayUponInvoiceRiskManagementTest extends TestCase
{
    const SHOP_ID = 591790496;

    /**
     * The returnValueProvider provides a testcase to assert the check fails in
     * case earlier risk management checks failed already.
     *
     * @dataProvider returnValueProvider
     *
     * The paymentMethodProvider provides testcases to assert the check
     * succeeds for all payment methods besides
     * PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME.
     *
     * @dataProvider paymentMethodProvider
     *
     * @param bool $expectedReturnValue
     *
     * @return void
     */
    public function testRiskManagementCheck(
        PayUponInvoiceRiskManagement $subject,
        Enlight_Hook_HookArgs $args,
        $expectedReturnValue
    ) {
        $actualReturnValue = $subject->afterManageRisks($args);

        if ($expectedReturnValue) {
            static::assertTrue($actualReturnValue, 'Risk management check should have failed.');
        } else {
            static::assertFalse($actualReturnValue, 'Risk management check should have succeeded.');
        }
    }

    /**
     * This method asserts that the risk rule is actually executed, once we made
     * sure it's applicable.
     *
     * @return void
     */
    public function testRiskRuleIsExecutedWhenBasicChecksPass()
    {
        $adminMock = static::createMock(sAdmin::class);

        $adminMock->expects(static::once())
            ->method('executeRiskRule')
            ->with(
                'PayPalUnifiedInvoiceRiskManagementRule',
                static::anything(),
                static::anything(),
                static::anything(),
                static::anything()
            );

        $argsMock = static::createMock(Enlight_Hook_HookArgs::class);

        $argsMock->method('get')
            ->willReturnMap([
                ['basket', 'fd845875-4ddf-41a6-bbdf-952a20aa598f'],
                ['paymentID', 101],
            ]);

        $argsMock->method('getSubject')
            ->willReturn($adminMock);

        $paymentMethodProviderMock = $this->getPaymentMethodProvider();

        $paymentMethodProviderMock->method('getPaymentId')
            ->willReturn(101);

        $subject = $this->getPayUponInvoiceRiskManagement($paymentMethodProviderMock);

        $subject->afterManageRisks($argsMock);
    }

    /**
     * Asserts that the constraints used are equal to the ones required by
     * PayPal.
     *
     * @see https://developer.paypal.com/docs/limited-release/alternative-payment-methods-with-orders/pay-upon-invoice/integrate-pui-partners/
     *
     * @return void
     */
    public function testConstraintsAreCorrect()
    {
        $argsMock = static::createMock(Enlight_Event_EventArgs::class);

        $validatorMock = $this->getValidator();

        $validatorMock->expects(static::once())
            ->method('validate')
            ->with(
                static::anything(),
                static::callback(self::getConstraintCollectionValidator())
            );

        $subject = $this->getPayUponInvoiceRiskManagement(null, null, $validatorMock);

        $subject->onExecuteRule($argsMock);
    }

    /**
     * @return \Generator<array<mixed>>
     */
    public function returnValueProvider()
    {
        $argsMock = static::createMock(Enlight_Hook_HookArgs::class);

        /*
         * Supposed another validation failed already, the return value will be
         * `true`.
         */
        $argsMock->method('getReturn')
            ->willReturn(true);

        yield 'Another risk management rule check already failed' => [
            $this->getPayUponInvoiceRiskManagement(),
            $argsMock,
            true,
        ];
    }

    /**
     * @return \Generator<array<mixed>>
     */
    public function paymentMethodProvider()
    {
        $paymentMethodProviderMock = $this->getPaymentMethodProvider();

        $paymentMethodProviderMock->method('getPaymentId')
            ->willReturnMap(self::getPaymentMethodMap());

        foreach (self::getPaymentMethodMap() as list($paymentMethod, $paymentMethodId)) {
            $paymentMethodDescription = $paymentMethod instanceof Constraint ? \get_class($paymentMethod) : $paymentMethod;
            $isPayUponInvoice = $paymentMethod === PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME;

            $argsMock = static::createMock(Enlight_Hook_HookArgs::class);

            $argsMock->method('get')
                ->willReturnMap([
                    ['basket', 'fd845875-4ddf-41a6-bbdf-952a20aa598f'],
                    ['paymentID', $paymentMethodId],
                ]);

            if ($isPayUponInvoice) {
                $argsMock->method('getSubject')
                    ->willReturn(static::createConfiguredMock(sAdmin::class, [
                        'executeRiskRule' => true,
                    ]));
            }

            yield sprintf('Payment method is %s', $paymentMethodDescription) => [
                $this->getPayUponInvoiceRiskManagement($paymentMethodProviderMock),
                $argsMock,
                $isPayUponInvoice,
            ];
        }
    }

    /**
     * @param PaymentMethodProvider|null    $paymentMethodProvider
     * @param DependencyProvider|null       $dependencyProvider
     * @param ValidatorInterface|null       $validator
     * @param ContextServiceInterface|null  $contextService
     * @param SettingsServiceInterface|null $settingsService
     *
     * @return PayUponInvoiceRiskManagement
     */
    protected function getPayUponInvoiceRiskManagement(
        $paymentMethodProvider = null,
        $dependencyProvider = null,
        $validator = null,
        $contextService = null,
        $settingsService = null
    ) {
        return new PayUponInvoiceRiskManagement(
            $paymentMethodProvider ?: $this->getPaymentMethodProvider(),
            $dependencyProvider ?: $this->getDependencyProvider(),
            $validator ?: $this->getValidator(),
            $contextService ?: $this->getContextService(),
            $settingsService ?: $this->getSettingsService()
        );
    }

    /**
     * @return MockObject|PaymentMethodProvider
     */
    protected function getPaymentMethodProvider()
    {
        return static::createMock(PaymentMethodProvider::class);
    }

    /**
     * @return MockObject|DependencyProvider
     */
    protected function getDependencyProvider()
    {
        $dependencyProviderMock = static::createMock(DependencyProvider::class);

        $dependencyProviderMock->method('getFront')
            ->willReturn(static::createConfiguredMock(Enlight_Controller_Front::class, [
                'Request' => static::createMock(Enlight_Controller_Request_Request::class),
            ]));

        return $dependencyProviderMock;
    }

    /**
     * @return MockObject|ValidatorInterface
     */
    protected function getValidator()
    {
        $validatorMock = static::createMock(ValidatorInterface::class);

        $validatorMock->method('validate')
            ->willReturn(static::createMock(ConstraintViolationListInterface::class));

        return $validatorMock;
    }

    /**
     * @return MockObject|ContextServiceInterface
     */
    protected function getContextService()
    {
        $contextServiceMock = static::createMock(ContextServiceInterface::class);

        $contextServiceMock->method('getShopContext')
            ->willReturn(static::createConfiguredMock(ShopContextInterface::class, [
                'getCurrency' => static::createConfiguredMock(Currency::class, [
                    'getCurrency' => 'c63432e0-e10c-4ca0-aae9-e622b43d0285',
                ]),
                'getShop' => static::createConfiguredMock(Shop::class, [
                    'getId' => self::SHOP_ID,
                ]),
            ]));

        return $contextServiceMock;
    }

    /**
     * @return MockObject|SettingsServiceInterface
     */
    protected function getSettingsService()
    {
        $settingsServiceMock = static::createMock(SettingsServiceInterface::class);

        $settingsServiceMock->method('getSettings')
            ->willReturnMap([
                [self::SHOP_ID, SettingsTable::GENERAL, (new General())->fromArray(['shopId' => self::SHOP_ID])],
                [self::SHOP_ID, SettingsTable::PAY_UPON_INVOICE, (new PayUponInvoice())->fromArray(['shopId' => self::SHOP_ID, 'active' => true, 'onboardingCompleted' => true])],
            ]);

        return $settingsServiceMock;
    }

    /**
     * @return array<array<string|Constraint|int>>
     */
    protected static function getPaymentMethodMap()
    {
        return [
            [PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, 100],
            [PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME, 101],
            [PaymentMethodProviderInterface::PAYPAL_UNIFIED_INSTALLMENTS_METHOD_NAME, 102],
            [static::anything(), 0],
        ];
    }

    /**
     * @return Closure
     */
    protected static function getConstraintCollectionValidator()
    {
        return static function (Collection $constraintCollection) {
            static::assertInstanceOf(Collection::class, $constraintCollection);
            static::assertCount(4, $constraintCollection->fields);

            foreach ($constraintCollection->fields as $field => $constraint) {
                $actualConstraint = $constraint->constraints[0];

                if ($field === 'country') {
                    static::assertInstanceOf(EqualTo::class, $actualConstraint);
                    static::assertSame('DE', $actualConstraint->value);
                } elseif ($field === 'currency') {
                    static::assertInstanceOf(EqualTo::class, $actualConstraint);
                    static::assertSame('EUR', $actualConstraint->value);
                } elseif ($field === 'amount') {
                    static::assertInstanceOf(Range::class, $actualConstraint);
                    static::assertSame(5.0, $actualConstraint->min);
                    static::assertSame(2500.0, $actualConstraint->max);
                } elseif ($field === 'phoneNumber') {
                    static::assertInstanceOf(NotBlank::class, $actualConstraint);
                } else {
                    return false;
                }
            }

            return true;
        };
    }
}
