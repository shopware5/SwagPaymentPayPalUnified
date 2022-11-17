<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Components_Session_Namespace;
use Enlight_Controller_ActionEventArgs;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Generator;
use PDO;
use PHPUnit\Framework\TestCase;
use Shopware_Components_Config;
use Shopware_Controllers_Frontend_Checkout;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\Services\LoggerService;
use SwagPaymentPayPalUnified\Components\Services\PhoneNumberService;
use SwagPaymentPayPalUnified\Subscriber\PayUponInvoice;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class PayUponInvoiceTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use ShopRegistrationTrait;

    const DATE_OF_BIRTH = '1970-01-01';
    const PHONE_NUMBER = '0256199785';
    const ANY_ID = 1;
    const CUSTOMER_ID = 999999;
    const ADDRESS_ID = 1000000;

    /**
     * @dataProvider onCheckoutTestDataProvider
     *
     * @param array<string,mixed> $sOrderVariables
     * @param array<string,mixed> $expectedResult
     *
     * @return void
     */
    public function testOnCheckout(Enlight_Controller_ActionEventArgs $args, array $sOrderVariables, array $expectedResult)
    {
        $session = $this->createMock(Enlight_Components_Session_Namespace::class);
        $session->method('offsetGet')->willReturn($sOrderVariables);

        $dependencyProvider = $this->createDependencyProvider($session);

        $subscriber = new PayUponInvoice(
            $dependencyProvider,
            $this->getContainer()->get('config'),
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('paypal_unified.phone_number_service')
        );

        $subscriber->onCheckout($args);

        $viewAssign = $args->getSubject()->View()->getAssign();

        static::assertSame($expectedResult['expectLegalText'], $viewAssign['showPayUponInvoiceLegalText'], 'LegalText mismatch');
        static::assertSame($expectedResult['expectPhoneField'], $viewAssign['showPayUponInvoicePhoneField'], 'PhoneField mismatch');
        static::assertSame($expectedResult['expectBirthdayField'], $viewAssign['showPayUponInvoiceBirthdayField'], 'BirthdayField mismatch');
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function onCheckoutTestDataProvider()
    {
        yield 'ActionName and PaymentMethodName are empty' => [
            $this->createEnlightEventArgs(),
            ['sUserData' => ['billingaddress' => ['phone' => null], 'additional' => ['user' => ['birthday' => null]]]],
            ['expectLegalText' => null, 'expectPhoneField' => null, 'expectBirthdayField' => null],
        ];

        yield 'ActionName and PaymentMethodName does not match' => [
            $this->createEnlightEventArgs('anyAction', 'anyPaymentMethod'),
            ['sUserData' => ['billingaddress' => ['phone' => null], 'additional' => ['user' => ['birthday' => null]]]],
            ['expectLegalText' => null, 'expectPhoneField' => null, 'expectBirthdayField' => null],
        ];

        yield 'PaymentMethodName are empty' => [
            $this->createEnlightEventArgs('confirm'),
            ['sUserData' => ['billingaddress' => ['phone' => null], 'additional' => ['user' => ['birthday' => null]]]],
            ['expectLegalText' => null, 'expectPhoneField' => null, 'expectBirthdayField' => null],
        ];

        yield 'PaymentMethodName does not match' => [
            $this->createEnlightEventArgs('confirm', 'anyPaymentMethod'),
            ['sUserData' => ['billingaddress' => ['phone' => null], 'additional' => ['user' => ['birthday' => null]]]],
            ['expectLegalText' => null, 'expectPhoneField' => null, 'expectBirthdayField' => null],
        ];

        yield 'Should assign showPayUponInvoiceLegalText => true' => [
            $this->createEnlightEventArgs('confirm', PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME),
            ['sUserData' => ['billingaddress' => ['phone' => self::PHONE_NUMBER], 'additional' => ['user' => ['birthday' => self::DATE_OF_BIRTH]]]],
            ['expectLegalText' => true, 'expectPhoneField' => true, 'expectBirthdayField' => true],
        ];

        yield 'Should assign showPayUponInvoiceLegalText => true, showPayUponInvoicePhoneField => true' => [
            $this->createEnlightEventArgs('confirm', PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME),
            ['sUserData' => ['billingaddress' => ['phone' => null], 'additional' => ['user' => ['birthday' => self::DATE_OF_BIRTH]]]],
            ['expectLegalText' => true, 'expectPhoneField' => true, 'expectBirthdayField' => true],
        ];

        yield 'Should assign showPayUponInvoiceLegalText => true, showPayUponInvoiceBirthdayField => true because birthday is null' => [
            $this->createEnlightEventArgs('confirm', PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME),
            ['sUserData' => ['billingaddress' => ['phone' => self::PHONE_NUMBER], 'additional' => ['user' => ['birthday' => null]]]],
            ['expectLegalText' => true, 'expectPhoneField' => true, 'expectBirthdayField' => true],
        ];

        yield 'Should assign showPayUponInvoiceLegalText => true, showPayUponInvoiceBirthdayField => true because birthday is 0000-00-00' => [
            $this->createEnlightEventArgs('confirm', PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME),
            ['sUserData' => ['billingaddress' => ['phone' => self::PHONE_NUMBER], 'additional' => ['user' => ['birthday' => '0000-00-00']]]],
            ['expectLegalText' => true, 'expectPhoneField' => true, 'expectBirthdayField' => true],
        ];

        yield 'Should assign showPayUponInvoiceLegalText => true, showPayUponInvoicePhoneField => true, showPayUponInvoiceBirthdayField => true because phone and birthday are null' => [
            $this->createEnlightEventArgs('confirm', PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME),
            ['sUserData' => ['billingaddress' => ['phone' => null], 'additional' => ['user' => ['birthday' => null]]]],
            ['expectLegalText' => true, 'expectPhoneField' => true, 'expectBirthdayField' => true],
        ];

        yield 'Should assign showPayUponInvoiceLegalText => true, showPayUponInvoicePhoneField => true, showPayUponInvoiceBirthdayField => true because phone and birthday are empty strings' => [
            $this->createEnlightEventArgs('confirm', PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME),
            ['sUserData' => ['billingaddress' => ['phone' => ''], 'additional' => ['user' => ['birthday' => '']]]],
            ['expectLegalText' => true, 'expectPhoneField' => true, 'expectBirthdayField' => true],
        ];
    }

    /**
     * @dataProvider handleExtraDataOnCheckoutTestDataProvider
     *
     * @param array<string,mixed> $sOrderVariables
     * @param bool                $expectsBirthday
     * @param bool                $expectsPhoneNUmber
     *
     * @return void
     */
    public function testHandleExtraDataOnCheckout(Enlight_Controller_ActionEventArgs $args, array $sOrderVariables, $expectsBirthday, $expectsPhoneNUmber)
    {
        $dependencyProvider = $this->getContainer()->get('paypal_unified.dependency_provider');
        $session = $dependencyProvider->getSession();
        $session->offsetSet('sOrderVariables', $sOrderVariables);

        $subscriber = new PayUponInvoice(
            $dependencyProvider,
            $this->getContainer()->get('config'),
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('paypal_unified.phone_number_service')
        );

        $subscriber->onCheckout($args);

        $userDataResult = $session->offsetGet('sOrderVariables')['sUserData'];
        $saveExtraFieldsResult = $session->offsetGet('puiExtraFields');

        static::assertSame(self::ANY_ID, $saveExtraFieldsResult['customerId']);
        static::assertSame(self::ANY_ID, $saveExtraFieldsResult['billingAddressId']);

        if ($expectsBirthday) {
            static::assertSame(self::DATE_OF_BIRTH, $userDataResult['additional']['user']['birthday'], 'sOrderVariables -> birthday is not updated');
            static::assertSame(self::DATE_OF_BIRTH, $saveExtraFieldsResult['dateOfBirth'], 'puiExtraFields -> birthday is not set');
        } else {
            static::assertEmpty($userDataResult['additional']['user']['birthday']);
            static::assertEmpty($saveExtraFieldsResult['dateOfBirth']);
        }

        if ($expectsPhoneNUmber) {
            static::assertSame(self::PHONE_NUMBER, $userDataResult['billingaddress']['phone'], 'sOrderVariables -> phone is not updated');
            static::assertSame(self::PHONE_NUMBER, $saveExtraFieldsResult['phoneNumber'], 'puiExtraFields -> phoneNumber is not set');
        } else {
            static::assertEmpty($userDataResult['billingaddress']['phone']);
            static::assertEmpty($saveExtraFieldsResult['phoneNumber']);
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function handleExtraDataOnCheckoutTestDataProvider()
    {
        yield 'Should set session vars -- birthday and phone number are null' => [
            $this->createEnlightEventArgs(
                'payment',
                PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME,
                $this->createRequest()
            ),
            ['sUserData' => ['billingaddress' => ['id' => self::ANY_ID, 'phone' => null], 'additional' => ['user' => ['id' => self::ANY_ID, 'birthday' => null]]]],
            false,
            false,
        ];

        yield 'Should set session vars -- birthday and phone number are empty strings' => [
            $this->createEnlightEventArgs(
                'payment',
                PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME,
                $this->createRequest()
            ),
            ['sUserData' => ['billingaddress' => ['id' => self::ANY_ID, 'phone' => ''], 'additional' => ['user' => ['id' => self::ANY_ID, 'birthday' => '']]]],
            false,
            false,
        ];

        yield 'Should set session vars -- birthday is set and phone number is null' => [
            $this->createEnlightEventArgs(
                'payment',
                PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME,
                $this->createRequest(true)
            ),
            ['sUserData' => ['billingaddress' => ['id' => self::ANY_ID, 'phone' => null], 'additional' => ['user' => ['id' => self::ANY_ID, 'birthday' => null]]]],
            true,
            false,
        ];

        yield 'Should set session vars -- birthday is set as array and phone number is null' => [
            $this->createEnlightEventArgs(
                'payment',
                PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME,
                $this->createRequest(false, true)
            ),
            ['sUserData' => ['billingaddress' => ['id' => self::ANY_ID, 'phone' => null], 'additional' => ['user' => ['id' => self::ANY_ID, 'birthday' => null]]]],
            true,
            false,
        ];

        yield 'Should set session vars -- phone number is set and birthday is null' => [
            $this->createEnlightEventArgs(
                'payment',
                PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME,
                $this->createRequest(false, false, true)
            ),
            ['sUserData' => ['billingaddress' => ['id' => self::ANY_ID, 'phone' => null], 'additional' => ['user' => ['id' => self::ANY_ID, 'birthday' => null]]]],
            false,
            true,
        ];

        yield 'Should set session vars -- phone number is set and birthday is set' => [
            $this->createEnlightEventArgs(
                'payment',
                PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME,
                $this->createRequest(true, false, true)
            ),
            ['sUserData' => ['billingaddress' => ['id' => self::ANY_ID, 'phone' => null], 'additional' => ['user' => ['id' => self::ANY_ID, 'birthday' => null]]]],
            true,
            true,
        ];

        yield 'Should set session vars -- phone number is set and birthday is set as array' => [
            $this->createEnlightEventArgs(
                'payment',
                PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME,
                $this->createRequest(false, true, true)
            ),
            ['sUserData' => ['billingaddress' => ['id' => self::ANY_ID, 'phone' => null], 'additional' => ['user' => ['id' => self::ANY_ID, 'birthday' => null]]]],
            true,
            true,
        ];
    }

    /**
     * @dataProvider onFinishTestDataProvider
     *
     * @param string|null                     $actionName
     * @param array<string,mixed>|null        $extraFieldsData
     * @param Shopware_Components_Config|null $config
     * @param string|null                     $expectedBirthday
     * @param string|null                     $expectedPhoneNumber
     *
     * @return void
     */
    public function testOnFinish($actionName = null, $extraFieldsData = null, $config = null, $expectedBirthday = null, $expectedPhoneNumber = null)
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/customer_update_phone_and_birthday.sql');
        static::assertTrue(\is_string($sql));

        $connection = $this->getContainer()->get('dbal_connection');
        $connection->exec($sql);

        $dependencyProvider = $this->getContainer()->get('paypal_unified.dependency_provider');
        $session = $dependencyProvider->getSession();
        $session->offsetSet('puiExtraFields', $extraFieldsData);

        if ($config === null) {
            $config = $this->getContainer()->get('config');
        }

        $subscriber = new PayUponInvoice(
            $dependencyProvider,
            $config,
            $this->getContainer()->get('dbal_connection'),
            new PhoneNumberService(
                $this->createMock(LoggerService::class),
                $this->getContainer()->get('dbal_connection'),
                $config
            )
        );

        $request = new Enlight_Controller_Request_RequestTestCase();
        if ($actionName !== null) {
            $request->setActionName($actionName);
        }

        $eventArgs = $this->createMock(Enlight_Controller_ActionEventArgs::class);
        $eventArgs->method('getRequest')->willReturn($request);

        $subscriber->onFinish($eventArgs);

        $birthdayResult = $connection->createQueryBuilder()
            ->select(['birthday'])
            ->from('s_user')
            ->where('id = :customerId')
            ->setParameter('customerId', self::CUSTOMER_ID)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);

        static::assertSame($expectedBirthday, $birthdayResult);

        $phoneNumberResult = $connection->createQueryBuilder()
            ->select(['phone'])
            ->from('s_user_addresses')
            ->where('id = :addressId')
            ->setParameter('addressId', self::ADDRESS_ID)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);

        static::assertSame($expectedPhoneNumber, $phoneNumberResult);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function onFinishTestDataProvider()
    {
        yield 'Should return because action is null' => [
            null,
            null,
            null,
            null,
            null,
        ];

        yield 'Should return because action is not like finish' => [
            'anyAction',
            null,
            null,
            null,
            null,
        ];

        yield 'Should return because sessionData is not set' => [
            'finish',
            null,
            null,
            null,
            null,
        ];

        yield 'Should not save data because the fields are not shown' => [
            'finish',
            [
                'customerId' => self::CUSTOMER_ID,
                'billingAddressId' => self::ADDRESS_ID,
                'dateOfBirth' => self::DATE_OF_BIRTH,
                'phoneNumber' => self::PHONE_NUMBER,
            ],
            $this->createConfig(false, false),
            null,
            null,
        ];

        yield 'Should save dateOfBirth' => [
            'finish',
            [
                'customerId' => self::CUSTOMER_ID,
                'billingAddressId' => self::ADDRESS_ID,
                'dateOfBirth' => self::DATE_OF_BIRTH,
                'phoneNumber' => self::PHONE_NUMBER,
            ],
            $this->createConfig(true, false),
            self::DATE_OF_BIRTH,
            null,
        ];

        yield 'Should save dateOfBirth and phoneNumber' => [
            'finish',
            [
                'customerId' => self::CUSTOMER_ID,
                'billingAddressId' => self::ADDRESS_ID,
                'dateOfBirth' => self::DATE_OF_BIRTH,
                'phoneNumber' => self::PHONE_NUMBER,
            ],
            $this->createConfig(true, true),
            self::DATE_OF_BIRTH,
            self::PHONE_NUMBER,
        ];
    }

    /**
     * @param bool|null $addDateOfBirth
     * @param bool|null $addDateOfBirthAsArray
     * @param bool|null $addPhoneNumber
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    private function createRequest($addDateOfBirth = false, $addDateOfBirthAsArray = false, $addPhoneNumber = false)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();

        if ($addDateOfBirth) {
            $request->setParam('puiDateOfBirth', '1970-01-01');
        }

        if ($addDateOfBirthAsArray) {
            $request->setParam('puiDateOfBirth', [
                'year' => '1970',
                'month' => '01',
                'day' => '01',
            ]);
        }

        if ($addPhoneNumber) {
            $request->setParam('puiTelephoneNumber', self::PHONE_NUMBER);
        }

        return $request;
    }

    /**
     * @param string|null                                     $actionName
     * @param string|null                                     $paymentMethodName
     * @param Enlight_Controller_Request_RequestTestCase|null $request
     *
     * @return Enlight_Controller_ActionEventArgs
     */
    private function createEnlightEventArgs($actionName = null, $paymentMethodName = null, $request = null)
    {
        $sPayment = ['name' => $paymentMethodName];

        $view = new Enlight_View_Default(new Enlight_Template_Manager());
        $view->assign('sPayment', $sPayment);

        if ($request === null) {
            $request = new Enlight_Controller_Request_RequestTestCase();
        }

        if ($actionName !== null) {
            $request->setActionName($actionName);
        }

        $controller = $this->createMock(Shopware_Controllers_Frontend_Checkout::class);
        $controller->method('View')->willReturn($view);

        $eventArgs = new Enlight_Controller_ActionEventArgs();
        $eventArgs->set('request', $request);
        $eventArgs->set('subject', $controller);

        return $eventArgs;
    }

    /**
     * @return DependencyProvider
     */
    private function createDependencyProvider(Enlight_Components_Session_Namespace $session)
    {
        $dependencyProvider = $this->createMock(DependencyProvider::class);
        $dependencyProvider->method('getSession')->willReturn($session);

        return $dependencyProvider;
    }

    /**
     * @param bool $showBirthdayField
     * @param bool $showPhoneNumberField
     *
     * @return Shopware_Components_Config
     */
    private function createConfig($showBirthdayField, $showPhoneNumberField)
    {
        $config = $this->createMock(Shopware_Components_Config::class);

        $config->method('get')->willReturnMap([
            ['showbirthdayfield', null, $showBirthdayField],
            ['showphonenumberfield', null, $showPhoneNumberField],
        ]);

        return $config;
    }
}
