<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Closure;
use Enlight_Components_Session_Namespace;
use Enlight_Controller_Front;
use Enlight_Controller_Request_Request;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Event_EventArgs;
use Enlight_Hook_HookArgs;
use Generator;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use sAdmin;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Currency;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Config as ShopwareConfig;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\Models\Settings\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\Subscriber\PayUponInvoiceRiskManagement;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PayUponInvoiceRiskManagementTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;

    const SHOP_ID = 591790496;
    const PAYMENT_ID_PUI = 101;

    /**
     * The returnValueProvider provides a testcase to assert the check fails in
     * case earlier risk management checks failed already.
     *
     * @dataProvider returnValueProvider
     *
     * The paymentMethodProvider provides testcases to assert the check
     * succeeds for all payment methods besides
     * PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME.
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
        $adminMock = $this->createMock(sAdmin::class);

        $adminMock->expects(static::once())
            ->method('executeRiskRule')
            ->with(
                'PayPalUnifiedInvoiceRiskManagementRule',
                static::anything(),
                static::anything(),
                static::anything(),
                static::anything()
            );

        $argsMock = $this->createMock(Enlight_Hook_HookArgs::class);

        $argsMock->method('get')
            ->willReturnMap([
                ['basket', 'fd845875-4ddf-41a6-bbdf-952a20aa598f'],
                ['paymentID', self::PAYMENT_ID_PUI],
            ]);

        $argsMock->method('getSubject')
            ->willReturn($adminMock);

        $paymentMethodProviderMock = $this->getPaymentMethodProvider();

        $paymentMethodProviderMock->method('getPaymentId')
            ->willReturn(self::PAYMENT_ID_PUI);

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
        $argsMock = $this->createMock(Enlight_Event_EventArgs::class);
        $argsMock->method('get')->willReturnMap([
            [
                'user', [
                    'additional' => [
                        'country' => ['countryiso' => 'DE'],
                        'user' => ['birthday' => '1970-01-01'],
                    ],
                    'billingaddress' => ['phone' => '01519999999'],
                ],
            ],
        ]);

        $validatorMock = $this->getValidator();

        $validatorMock->expects(static::once())
            ->method('validate')
            ->with(
                static::anything(),
                static::callback(self::getConstraintCollectionValidator())
            );

        $subject = $this->getPayUponInvoiceRiskManagement(null, $validatorMock);

        $subject->onExecuteRule($argsMock);
    }

    /**
     * @return Generator<array{0: PayUponInvoiceRiskManagement, 1: Enlight_Hook_HookArgs, 2: true}>
     */
    public function returnValueProvider()
    {
        $argsMock = $this->createMock(Enlight_Hook_HookArgs::class);

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
     * @return Generator<array{0: PayUponInvoiceRiskManagement, 1: Enlight_Hook_HookArgs, 2: bool}>
     */
    public function paymentMethodProvider()
    {
        $paymentMethodProviderMock = $this->getPaymentMethodProvider();

        $paymentMethodProviderMock->method('getPaymentId')
            ->willReturnMap(self::getPaymentMethodMap());

        foreach (self::getPaymentMethodMap() as list($paymentMethod, $paymentMethodId)) {
            $paymentMethodDescription = $paymentMethod instanceof Constraint ? \get_class($paymentMethod) : $paymentMethod;
            $isPayUponInvoice = $paymentMethod === PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME;

            $argsMock = $this->createMock(Enlight_Hook_HookArgs::class);

            $argsMock->method('get')
                ->willReturnMap([
                    ['basket', 'fd845875-4ddf-41a6-bbdf-952a20aa598f'],
                    ['paymentID', $paymentMethodId],
                ]);

            if ($isPayUponInvoice) {
                $argsMock->method('getSubject')
                    ->willReturn($this->createConfiguredMock(sAdmin::class, [
                        'executeRiskRule' => true,
                    ]));
            }

            yield \sprintf('Payment method is %s', $paymentMethodDescription) => [
                $this->getPayUponInvoiceRiskManagement($paymentMethodProviderMock),
                $argsMock,
                $isPayUponInvoice,
            ];
        }
    }

    /**
     * @return void
     */
    public function testShouldShowUnconditionally()
    {
        $paymentMethodProviderMock = $this->getPaymentMethodProvider();
        $paymentMethodProviderMock->method('getPaymentId')->willReturn(self::PAYMENT_ID_PUI);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('checkout');
        $request->setActionName('shippingPayment');
        $dependencyProvider = $this->getDependencyProvider($request);

        $argsMock = $this->createMock(Enlight_Hook_HookArgs::class);
        $argsMock->method('get')->willReturnMap([['paymentID', self::PAYMENT_ID_PUI]]);
        $argsMock->method('getSubject')->willReturn(
            $this->getContainer()->get('paypal_unified.dependency_provider')->getModule('sAdmin')
        );

        $subscriber = $this->getPayUponInvoiceRiskManagement($paymentMethodProviderMock, null, $dependencyProvider);
        static::assertFalse($subscriber->afterManageRisks($argsMock));
    }

    /**
     * @dataProvider onExecuteRuleShouldAssignViolationListTestDataProvider
     *
     * @param array<string,mixed> $user
     * @param array<string,float> $basket
     * @param array<string,mixed> $expectedResult
     *
     * @return void
     */
    public function testOnExecuteRuleShouldAssignViolationList(array $user, array $basket, array $expectedResult)
    {
        $session = $this->createMock(Enlight_Components_Session_Namespace::class);

        $paymentMethodProvider = $this->createMock(PaymentMethodProviderInterface::class);
        $paymentMethodProvider->expects(static::once())->method('getPaymentId')->willReturn(1);

        $dependencyProvider = $this->getDependencyProvider(null, $session);
        $subscriber = $this->getPayUponInvoiceRiskManagement(
            $paymentMethodProvider,
            $this->getContainer()->get('validator'),
            $dependencyProvider
        );

        $argsMock = $this->createMock(Enlight_Event_EventArgs::class);
        $argsMock->method('get')->willReturnMap([
            ['user', $user],
            ['basket', $basket],
            ['paymentID', 1],
        ]);

        $subscriber->onExecuteRule($argsMock);
    }

    /**
     * @return Generator<array{array<string,mixed>}>
     */
    public function onExecuteRuleShouldAssignViolationListTestDataProvider()
    {
        yield 'Risk management rule check amount' => [
            [
                'additional' => [
                    'country' => ['countryiso' => 'DE'],
                    'user' => ['birthday' => '1970-01-01'],
                ],
                'billingaddress' => ['phone' => '01519999999'],
            ],
            ['AmountNumeric' => 1.99],
            ['key' => PayUponInvoiceRiskManagement::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_ERROR_LIST_KEY, 'value' => ['[amount]']],
        ];

        yield 'Risk management rule check amount, country' => [
            [
                'additional' => [
                    'country' => ['countryiso' => 'US'],
                    'user' => ['birthday' => null],
                ],
                'billingaddress' => ['phone' => '01519999999'],
            ],
            ['AmountNumeric' => 1.99],
            ['key' => PayUponInvoiceRiskManagement::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_ERROR_LIST_KEY, 'value' => ['[country]', '[amount]']],
        ];
    }

    /**
     * @return void
     */
    public function testOnExecuteRuleShouldUseExtendedValidation()
    {
        $front = $this->getContainer()->get('paypal_unified.dependency_provider')->getFront();
        static::assertInstanceOf(Enlight_Controller_Front::class, $front);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('checkout');
        $request->setActionName('payment');

        $front->setRequest($request);

        $argsMock = $this->createMock(Enlight_Event_EventArgs::class);
        $argsMock->method('get')->willReturnMap([
            ['user', ['additional' => ['country' => ['countryiso' => 'DE']]]],
            ['basket', ['AmountNumeric' => 1.00]],
            ['paymentID', 1],
        ]);

        $paymentMethodProviderMock = $this->getPaymentMethodProvider();
        $paymentMethodProviderMock->method('getPaymentId')->willReturn(1);

        $subject = $this->getPayUponInvoiceRiskManagement(
            $paymentMethodProviderMock,
            $this->getContainer()->get('validator'),
            $this->getContainer()->get('paypal_unified.dependency_provider')
        );

        static::assertTrue($subject->onExecuteRule($argsMock));
    }

    /**
     * @dataProvider checkForMissingTechnicalRequirementsDataProvider
     *
     * @param ShopwareConfig|null           $config
     * @param SettingsServiceInterface|null $settings
     *
     * @return void
     */
    public function testCheckForMissingTechnicalRequirements($config, $settings)
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->method('getPaymentId')->willReturn(1);

        $subject = $this->getPayUponInvoiceRiskManagement(
            $paymentMethodProvider,
            null,
            null,
            null,
            $settings
        );
        $argsMock = $this->createMock(Enlight_Event_EventArgs::class);
        $argsMock->method('get')->willReturnMap([
            ['user', ['additional' => ['country' => ['countryiso' => 'DE']]]],
            ['basket', ['AmountNumeric' => 1.00]],
            ['paymentID', 1],
        ]);

        static::assertTrue($subject->onExecuteRule($argsMock));
    }

    /**
     * @return Generator<string, array{0: ShopwareConfig|null, 1: SettingsServiceInterface|null}>
     */
    public function checkForMissingTechnicalRequirementsDataProvider()
    {
        $settingsServiceMock = $this->createMock(SettingsServiceInterface::class);
        $settingsServiceMock->method('getSettings')
            ->willReturnMap([
                [
                    self::SHOP_ID,
                    SettingsTable::GENERAL,
                    null,
                ],
            ]);

        yield 'Test no general settings' => [
            null,
            $settingsServiceMock,
        ];

        $settingsServiceMock = $this->createMock(SettingsServiceInterface::class);
        $settingsServiceMock->method('getSettings')
            ->willReturnMap([
                [
                    self::SHOP_ID,
                    SettingsTable::GENERAL,
                    (new General())->fromArray(['shopId' => self::SHOP_ID]),
                ],
                [
                    self::SHOP_ID,
                    SettingsTable::PAY_UPON_INVOICE,
                    null,
                ],
            ]);

        yield 'Test no pui settings' => [
            null,
            $settingsServiceMock,
        ];

        $settingsServiceMock = $this->createMock(SettingsServiceInterface::class);
        $settingsServiceMock->method('getSettings')
            ->willReturnMap([
                [
                    self::SHOP_ID,
                    SettingsTable::GENERAL,
                    (new General())->fromArray(['shopId' => self::SHOP_ID]),
                ],
                [
                    self::SHOP_ID,
                    SettingsTable::PAY_UPON_INVOICE,
                    (new PayUponInvoice())->fromArray([
                        'shopId' => self::SHOP_ID,
                        'active' => false,
                        'onboardingCompleted' => true,
                    ]),
                ],
            ]);

        yield 'Test pui inactive' => [
            null,
            $settingsServiceMock,
        ];

        $settingsServiceMock = $this->createMock(SettingsServiceInterface::class);
        $settingsServiceMock->method('getSettings')
            ->willReturnMap([
                [
                    self::SHOP_ID,
                    SettingsTable::GENERAL,
                    (new General())->fromArray(['shopId' => self::SHOP_ID]),
                ],
                [
                    self::SHOP_ID,
                    SettingsTable::PAY_UPON_INVOICE,
                    (new PayUponInvoice())->fromArray([
                        'shopId' => self::SHOP_ID,
                        'active' => true,
                        'onboardingCompleted' => false,
                    ]),
                ],
            ]);

        yield 'Test pui not completely onboarded' => [
            null,
            $settingsServiceMock,
        ];
    }

    /**
     * @return void
     */
    public function testCheckUserIsNotSet()
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->method('getPaymentId')->willReturn(1);

        $riskManagementSubscriber = $this->getPayUponInvoiceRiskManagement(
            $paymentMethodProvider
        );

        $argsMock = $this->createMock(Enlight_Event_EventArgs::class);
        $argsMock->method('get')->willReturnMap([
            ['user', null],
            ['basket', ['AmountNumeric' => 1.00]],
            ['paymentID', 1],
        ]);

        static::assertFalse($riskManagementSubscriber->onExecuteRule($argsMock));
    }

    /**
     * @param PaymentMethodProviderInterface|null $paymentMethodProvider
     * @param ValidatorInterface|null             $validator
     * @param DependencyProvider|null             $dependencyProvider
     * @param ContextServiceInterface|null        $contextService
     * @param SettingsServiceInterface|null       $settingsService
     *
     * @return PayUponInvoiceRiskManagement
     */
    private function getPayUponInvoiceRiskManagement(
        $paymentMethodProvider = null,
        $validator = null,
        $dependencyProvider = null,
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
    private function getPaymentMethodProvider()
    {
        return $this->createMock(PaymentMethodProvider::class);
    }

    /**
     * @param Enlight_Controller_Request_Request|null   $request
     * @param Enlight_Components_Session_Namespace|null $session
     *
     * @return MockObject|DependencyProvider
     */
    private function getDependencyProvider($request = null, $session = null)
    {
        $dependencyProviderMock = $this->createMock(DependencyProvider::class);

        $dependencyProviderMock->method('getFront')
            ->willReturn($this->createConfiguredMock(Enlight_Controller_Front::class, [
                'Request' => $request ?: $this->createMock(Enlight_Controller_Request_Request::class),
            ]));

        $dependencyProviderMock->method('getSession')
            ->willReturn($session ?: $this->createMock(Enlight_Components_Session_Namespace::class));

        return $dependencyProviderMock;
    }

    /**
     * @return MockObject|ValidatorInterface
     */
    private function getValidator()
    {
        $validatorMock = $this->createMock(ValidatorInterface::class);

        $validatorMock->method('validate')
            ->willReturn($this->createMock(ConstraintViolationListInterface::class));

        return $validatorMock;
    }

    /**
     * @return MockObject|ContextServiceInterface
     */
    private function getContextService()
    {
        $contextServiceMock = $this->createMock(ContextServiceInterface::class);

        $contextServiceMock->method('getShopContext')
            ->willReturn($this->createConfiguredMock(ShopContextInterface::class, [
                'getCurrency' => $this->createConfiguredMock(Currency::class, [
                    'getCurrency' => 'EUR',
                ]),
                'getShop' => $this->createConfiguredMock(Shop::class, [
                    'getId' => self::SHOP_ID,
                ]),
            ]));

        return $contextServiceMock;
    }

    /**
     * @return MockObject|SettingsServiceInterface
     */
    private function getSettingsService()
    {
        $settingsServiceMock = $this->createMock(SettingsServiceInterface::class);

        $settingsServiceMock->method('getSettings')
            ->willReturnMap([
                [
                    self::SHOP_ID,
                    SettingsTable::GENERAL,
                    (new General())->fromArray(['shopId' => self::SHOP_ID]),
                ],
                [
                    self::SHOP_ID,
                    SettingsTable::PAY_UPON_INVOICE,
                    (new PayUponInvoice())->fromArray([
                        'shopId' => self::SHOP_ID,
                        'active' => true,
                        'onboardingCompleted' => true,
                    ]),
                ],
            ]);

        return $settingsServiceMock;
    }

    /**
     * @return array<array<string|Constraint|int>>
     */
    private static function getPaymentMethodMap()
    {
        return [
            [PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, 100],
            [PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME, self::PAYMENT_ID_PUI],
            [PaymentMethodProviderInterface::PAYPAL_UNIFIED_INSTALLMENTS_METHOD_NAME, 102],
            [static::anything(), 0],
        ];
    }

    /**
     * @return Closure
     */
    private static function getConstraintCollectionValidator()
    {
        return static function (Collection $constraintCollection) {
            static::assertInstanceOf(Collection::class, $constraintCollection);
            static::assertCount(3, $constraintCollection->fields);

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
                } elseif ($field === 'birthday') {
                    static::assertInstanceOf(NotBlank::class, $constraint->constraints[0]);
                    static::assertInstanceOf(Date::class, $constraint->constraints[1]);
                } else {
                    return false;
                }
            }

            return true;
        };
    }
}
