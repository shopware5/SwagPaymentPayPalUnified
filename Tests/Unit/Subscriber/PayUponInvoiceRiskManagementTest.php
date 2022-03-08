<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Subscriber;

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
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PayUponInvoiceRiskManagementTest extends TestCase
{
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
     *
     * @dataProvider paymentMethodProvider
     *
     * @param bool $expectedReturnValue
     *
     * @return void
     */
    public function testRiskManagementCheck(
        PayUponInvoiceRiskManagement $subject,
        \Enlight_Hook_HookArgs $args,
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
        $adminMock = $this->createMock(\sAdmin::class);

        $adminMock->expects(static::once())
            ->method('executeRiskRule')
            ->with(
                'PayPalUnifiedInvoiceRiskManagementRule',
                static::anything(),
                static::anything(),
                static::anything(),
                static::anything()
            );

        $argsMock = $this->createMock(\Enlight_Hook_HookArgs::class);

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
        $argsMock = $this->createMock(\Enlight_Event_EventArgs::class);

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
     * @return \Generator<array{0: PayUponInvoiceRiskManagement, 1: \Enlight_Hook_HookArgs, 2: true}>
     */
    public function returnValueProvider()
    {
        $argsMock = $this->createMock(\Enlight_Hook_HookArgs::class);

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
     * @return \Generator<array{0: PayUponInvoiceRiskManagement, 1: \Enlight_Hook_HookArgs, 2: bool}>
     */
    public function paymentMethodProvider()
    {
        $paymentMethodProviderMock = $this->getPaymentMethodProvider();

        $paymentMethodProviderMock->method('getPaymentId')
            ->willReturnMap(self::getPaymentMethodMap());

        foreach (self::getPaymentMethodMap() as list($paymentMethod, $paymentMethodId)) {
            $paymentMethodDescription = $paymentMethod instanceof Constraint ? \get_class($paymentMethod) : $paymentMethod;
            $isPayUponInvoice = $paymentMethod === PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME;

            $argsMock = $this->createMock(\Enlight_Hook_HookArgs::class);

            $argsMock->method('get')
                ->willReturnMap([
                    ['basket', 'fd845875-4ddf-41a6-bbdf-952a20aa598f'],
                    ['paymentID', $paymentMethodId],
                ]);

            if ($isPayUponInvoice) {
                $argsMock->method('getSubject')
                    ->willReturn($this->createConfiguredMock(\sAdmin::class, [
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
     * @return void
     */
    public function testShouldShowUnconditionally()
    {
        $paymentMethodProviderMock = $this->getPaymentMethodProvider();
        $paymentMethodProviderMock->method('getPaymentId')->willReturn(self::PAYMENT_ID_PUI);

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('checkout');
        $request->setActionName('shippingPayment');
        $dependencyProvider = $this->getDependencyProvider($request);

        $argsMock = $this->createMock(\Enlight_Hook_HookArgs::class);
        $argsMock->method('get')->willReturnMap([['paymentID', self::PAYMENT_ID_PUI]]);

        $subscriber = $this->getPayUponInvoiceRiskManagement($paymentMethodProviderMock, null, $dependencyProvider);
        static::assertFalse($subscriber->afterManageRisks($argsMock));
    }

    /**
     * @return void
     */
    public function testOnPostDispatchCheckoutNoPaymentBlocked()
    {
        $subscriber = $this->getPayUponInvoiceRiskManagement();

        $view = new ViewMock(new \Enlight_Template_Manager());

        $controller = $this->createMock(\Shopware_Controllers_Frontend_Checkout::class);
        $controller->method('View')->willReturn($view);
        $eventArgs = $this->createMock(\Enlight_Controller_ActionEventArgs::class);
        $eventArgs->method('get')->willReturn($controller);

        $subscriber->onPostDispatchCheckout($eventArgs);

        static::assertNull($view->getAssign(PayUponInvoiceRiskManagement::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED));
    }

    /**
     * @return void
     */
    public function testOnPostDispatchCheckoutPuiTechnicallyBlocked()
    {
        $session = $this->createMock(\Enlight_Components_Session_Namespace::class);
        $session->expects(static::once())->method('offsetGet')->willReturnMap([
            [PayUponInvoiceRiskManagement::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED_TECHNICALLY, true],
        ]);
        $session->expects(static::once())->method('offsetUnset');
        $dependencyProvider = $this->getDependencyProvider(null, $session);
        $subscriber = $this->getPayUponInvoiceRiskManagement(null, null, $dependencyProvider);

        $view = new ViewMock(new \Enlight_Template_Manager());
        $view->assign('paymentBlocked', true);

        $controller = $this->createMock(\Shopware_Controllers_Frontend_Checkout::class);
        $controller->method('View')->willReturn($view);
        $eventArgs = $this->createMock(\Enlight_Controller_ActionEventArgs::class);
        $eventArgs->method('get')->willReturn($controller);

        $subscriber->onPostDispatchCheckout($eventArgs);

        static::assertNull($view->getAssign(PayUponInvoiceRiskManagement::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED));
    }

    /**
     * @return void
     */
    public function testOnPostDispatchCheckoutPuiBlocked()
    {
        $session = $this->createMock(\Enlight_Components_Session_Namespace::class);
        $session->expects(static::exactly(2))->method('offsetGet')->willReturnMap([
            [PayUponInvoiceRiskManagement::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED_TECHNICALLY, null],
            [PayUponInvoiceRiskManagement::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED, true],
        ]);
        $session->expects(static::once())->method('offsetUnset');
        $dependencyProvider = $this->getDependencyProvider(null, $session);
        $subscriber = $this->getPayUponInvoiceRiskManagement(null, null, $dependencyProvider);

        $view = new ViewMock(new \Enlight_Template_Manager());
        $view->assign('paymentBlocked', true);

        $controller = $this->createMock(\Shopware_Controllers_Frontend_Checkout::class);
        $controller->method('View')->willReturn($view);
        $eventArgs = $this->createMock(\Enlight_Controller_ActionEventArgs::class);
        $eventArgs->method('get')->willReturn($controller);

        $subscriber->onPostDispatchCheckout($eventArgs);

        static::assertTrue($view->getAssign(PayUponInvoiceRiskManagement::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED));
    }

    /**
     * @return void
     */
    public function testSetPaymentMethodBlockedFlagPaymentIsNotPui()
    {
        $session = $this->createMock(\Enlight_Components_Session_Namespace::class);
        $session->expects(static::never())->method('offsetSet');
        $dependencyProvider = $this->getDependencyProvider(null, $session);
        $subscriber = $this->getPayUponInvoiceRiskManagement(null, null, $dependencyProvider);

        $eventArgs = $this->createMock(\Enlight_Event_EventArgs::class);
        $eventArgs->expects(static::once())->method('get')->willReturnMap([
            ['name', PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME],
        ]);
        $subscriber->setPaymentMethodBlockedFlag($eventArgs);
    }

    /**
     * @return void
     */
    public function testSetPaymentMethodBlockedFlag()
    {
        $session = $this->createMock(\Enlight_Components_Session_Namespace::class);
        $session->expects(static::once())->method('offsetSet');
        $dependencyProvider = $this->getDependencyProvider(null, $session);
        $subscriber = $this->getPayUponInvoiceRiskManagement(null, null, $dependencyProvider);

        $eventArgs = $this->createMock(\Enlight_Event_EventArgs::class);
        $eventArgs->expects(static::once())->method('get')->willReturnMap([
            ['name', PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME],
        ]);
        $subscriber->setPaymentMethodBlockedFlag($eventArgs);
    }

    /**
     * @param PaymentMethodProvider|null $paymentMethodProvider
     * @param ValidatorInterface|null    $validator
     * @param DependencyProvider|null    $dependencyProvider
     *
     * @return PayUponInvoiceRiskManagement
     */
    private function getPayUponInvoiceRiskManagement(
        $paymentMethodProvider = null,
        $validator = null,
        $dependencyProvider = null
    ) {
        return new PayUponInvoiceRiskManagement(
            $paymentMethodProvider ?: $this->getPaymentMethodProvider(),
            $dependencyProvider ?: $this->getDependencyProvider(),
            $validator ?: $this->getValidator(),
            $this->getContextService(),
            $this->getSettingsService()
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
     * @param \Enlight_Controller_Request_Request|null $request
     * @param \Enlight_Components_Session_Namespace    $session
     *
     * @return MockObject|DependencyProvider
     */
    private function getDependencyProvider($request = null, $session = null)
    {
        $dependencyProviderMock = $this->createMock(DependencyProvider::class);

        $dependencyProviderMock->method('getFront')
            ->willReturn($this->createConfiguredMock(\Enlight_Controller_Front::class, [
                'Request' => $request ?: $this->createMock(\Enlight_Controller_Request_Request::class),
            ]));

        $dependencyProviderMock->method('getSession')
            ->willReturn($session ?: $this->createMock(\Enlight_Components_Session_Namespace::class));

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
                    'getCurrency' => 'c63432e0-e10c-4ca0-aae9-e622b43d0285',
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
     * @return \Closure
     */
    private static function getConstraintCollectionValidator()
    {
        return static function (Collection $constraintCollection) {
            static::assertInstanceOf(Collection::class, $constraintCollection);
            static::assertCount(5, $constraintCollection->fields);

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
