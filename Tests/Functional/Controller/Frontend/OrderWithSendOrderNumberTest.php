<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedApm.php';
require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedV2.php';
require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedV2ExpressCheckout.php';
require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedV2PayUponInvoice.php';
require_once __DIR__ . '/../../../../Controllers/Widgets/PaypalUnifiedV2AdvancedCreditDebitCard.php';

use Enlight_Components_Session_Namespace;
use Enlight_Controller_Request_RequestTestCase;
use Exception;
use Generator;
use ReflectionClass;
use Shopware_Controllers_Frontend_PaypalUnifiedApm;
use Shopware_Controllers_Frontend_PaypalUnifiedV2;
use Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout;
use Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice;
use Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacade;
use SwagPaymentPayPalUnified\Components\Services\CartRestoreService;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactoryInterface;
use SwagPaymentPayPalUnified\Components\Services\Validation\SimpleBasketValidator;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentControllerResults\HandleOrderWithSendOrderNumberResult;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class OrderWithSendOrderNumberTest extends PaypalPaymentControllerTestCase
{
    use SettingsHelperTrait;
    use ShopRegistrationTrait;

    const SESSION_ID = 'phpUnitTestSessionId';

    const TRANSACTION_ID = '3E630337S9748511R';

    /**
     * @before
     *
     * @return void
     */
    public function createBasket()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/basket.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);
    }

    /**
     * @after
     *
     * @return void
     */
    public function cleanUpDatabase()
    {
        $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->delete('s_order_basket')
            ->where('sessionID = :sessionId')
            ->setParameter('sessionId', self::SESSION_ID)
            ->execute();

        $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->delete('s_order')
            ->where('transactionID = :transactionId')
            ->setParameter('transactionId', self::TRANSACTION_ID)
            ->execute();

        $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->delete('swag_payment_paypal_unified_settings_general')
            ->where('shop_id = 1')
            ->execute();
    }

    /**
     * @dataProvider PaymentStatusWillSetAndBasketStatusWillRestoreTestDataProvider
     *
     * @param class-string<AbstractPaypalPaymentController> $controllerClassName
     * @param string                                        $methodToCall
     *
     * @return void
     */
    public function testPaymentStatusWillSetAndBasketStatusWillRestore($controllerClassName, $methodToCall)
    {
        $userData = require __DIR__ . '/../../../_fixtures/s_user_data.php';

        $session = $this->getContainer()->get('session');
        $session->offsetSet('id', self::SESSION_ID);
        $session->offsetSet('sessionId', self::SESSION_ID);
        $session->offsetSet('sUserId', 1);
        $session->offsetSet('sOrderVariables', $userData);

        $this->insertGeneralSettingsFromArray([
            'active' => 1,
            'send_order_number' => 1,
        ]);

        $controller = $this->getController(
            $controllerClassName,
            [
                self::SERVICE_DEPENDENCY_PROVIDER => $this->createDependencyProvider(),
                self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY => $this->createRedirectDataBuilderFactory(),
                self::SERVICE_ORDER_PARAMETER_FACADE => $this->createPayPalOrderParameterFacade(),
                self::SERVICE_ORDER_RESOURCE => $this->createOrderResource(),
                self::SERVICE_ORDER_FACTORY => $this->createOrderFactory(),
                self::SERVICE_SETTINGS_SERVICE => $this->createSettingService(),
                self::SERVICE_PAYMENT_STATUS_SERVICE => $this->createPaymentStatusServiceExpectsMethodCall(),
                self::SERVICE_CART_RESTORE_SERVICE => $this->createBasketRestoreServiceExpectMethodCalls(),
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $this->createBasketValidator(),
            ],
            $this->createRequest()
        );

        $controller->$methodToCall();

        $session->unsetAll();
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function PaymentStatusWillSetAndBasketStatusWillRestoreTestDataProvider()
    {
        yield 'Call Shopware_Controllers_Frontend_PaypalUnifiedV2 with returnAction' => [
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class, 'returnAction',
        ];

        yield 'Call Shopware_Controllers_Frontend_PaypalUnifiedApm with returnAction' => [
            Shopware_Controllers_Frontend_PaypalUnifiedApm::class, 'returnAction',
        ];

        yield 'Call Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout with expressCheckoutFinishAction' => [
            Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout::class, 'expressCheckoutFinishAction',
        ];

        yield 'Call Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice with indexAction' => [
            Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice::class, 'indexAction',
        ];

        yield 'Call Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard with captureAction' => [
            Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard::class, 'captureAction',
        ];
    }

    /**
     * @return void
     */
    public function testCaptureOrAuthorizeOrderShouldRestoreTheBasket()
    {
        $controller = $this->getController(
            Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard::class,
            [
                self::SERVICE_DEPENDENCY_PROVIDER => $this->createDependencyProvider(),
                self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY => $this->createRedirectDataBuilderFactory(),
                self::SERVICE_ORDER_PARAMETER_FACADE => $this->createPayPalOrderParameterFacade(),
                self::SERVICE_ORDER_RESOURCE => $this->createOrderResource(),
                self::SERVICE_ORDER_FACTORY => $this->createOrderFactory(),
                self::SERVICE_SETTINGS_SERVICE => $this->createSettingService(),
                self::SERVICE_PAYMENT_STATUS_SERVICE => $this->createPaymentStatusServiceExpectsMethodCall(),
                self::SERVICE_CART_RESTORE_SERVICE => $this->getContainer()->get('paypal_unified.cart_restore_service'),
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $this->createBasketValidator(),
            ],
            $this->createRequest()
        );

        $session = $this->getContainer()->get('session');
        $session->offsetSet('id', self::SESSION_ID);
        $session->offsetSet('sessionId', self::SESSION_ID);

        $result = new HandleOrderWithSendOrderNumberResult(true, '08154711', $this->getContainer()->get('paypal_unified.cart_restore_service')->getCartData());

        // Fake create order
        $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->delete('s_order_basket')
            ->where('sessionID = :sessionId')
            ->setParameter('sessionId', self::SESSION_ID)
            ->execute();

        $captureOrAuthorizeOrderReflectionMethod = (new ReflectionClass(Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard::class))->getMethod('captureOrAuthorizeOrder');
        $captureOrAuthorizeOrderReflectionMethod->setAccessible(true);

        // Execute test case and get results
        $result = $captureOrAuthorizeOrderReflectionMethod->invokeArgs($controller, [self::TRANSACTION_ID, $this->createPayPalOrder(), $result]);
        $cartResult = $this->getContainer()->get('paypal_unified.cart_restore_service')->getCartData();

        static::assertNull($result);
        static::assertCount(8, $cartResult);

        $session->unsetAll();
    }

    /**
     * @return void
     */
    public function testExpressCheckoutFinishActionTheBasketItemsDontDuplicate()
    {
        $userData = require __DIR__ . '/../../../_fixtures/s_user_data.php';

        $session = $this->getContainer()->get('session');
        $session->offsetSet('id', self::SESSION_ID);
        $session->offsetSet('sessionId', self::SESSION_ID);
        $session->offsetSet('sUserId', 1);
        $session->offsetSet('sOrderVariables', $userData);

        $this->insertGeneralSettingsFromArray([
            'active' => 1,
            'send_order_number' => 1,
        ]);

        $request = $this->createRequest();
        $request->setParam('paypalOrderId', self::TRANSACTION_ID);

        $cartRestoreService = $this->getContainer()->get('paypal_unified.cart_restore_service');

        $payPalOrder = $this->createPayPalOrder();

        $reflectionProperty = (new ReflectionClass(Order::class))->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($payPalOrder, null);

        $orderFactoryMock = $this->createMock(OrderFactory::class);
        $orderFactoryMock->method('createOrder')->willReturn($payPalOrder);

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout::class,
            [
                self::SERVICE_DEPENDENCY_PROVIDER => $this->createDependencyProvider(),
                self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY => $this->createRedirectDataBuilderFactory(),
                self::SERVICE_ORDER_PARAMETER_FACADE => $this->createPayPalOrderParameterFacade(),
                self::SERVICE_ORDER_RESOURCE => $this->createOrderResource(),
                self::SERVICE_ORDER_FACTORY => $orderFactoryMock,
                self::SERVICE_SETTINGS_SERVICE => $this->createSettingService(),
                self::SERVICE_PAYMENT_STATUS_SERVICE => $this->createPaymentStatusServiceExpectsMethodCall(),
                self::SERVICE_CART_RESTORE_SERVICE => $cartRestoreService,
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $this->createBasketValidator(),
            ],
            $request
        );

        $controller->expressCheckoutFinishAction();

        $cartData = $cartRestoreService->getCartData();

        static::assertCount(8, $cartData);

        $session->unsetAll();
    }

    /**
     * @return SettingsService
     */
    private function createSettingService()
    {
        $settingServiceMock = $this->createMock(SettingsService::class);
        $settingServiceMock->method('get')->willReturnMap([
            [SettingsServiceInterface::SETTING_GENERAL_SEND_ORDER_NUMBER, SettingsTable::GENERAL, 1],
            [SettingsServiceInterface::SETTING_GENERAL_INTENT, SettingsTable::GENERAL, PaymentIntentV2::CAPTURE],
        ]);

        return $settingServiceMock;
    }

    /**
     * @return OrderResource
     */
    private function createOrderResource()
    {
        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($this->createPayPalOrder());
        $orderResourceMock->method('create')->willReturn($this->createPayPalOrder());
        $orderResourceMock->method('update')->willThrowException(new Exception('phpUnitTestException'));
        $orderResourceMock->method('capture')->willThrowException(new Exception('phpUnitTestException'));

        return $orderResourceMock;
    }

    /**
     * @return PaymentStatusService
     */
    private function createPaymentStatusServiceExpectsMethodCall()
    {
        $paymentStatusServiceMock = $this->createMock(PaymentStatusService::class);
        $paymentStatusServiceMock->expects(static::once())->method('setOrderAndPaymentStatusForFailedOrder');

        return $paymentStatusServiceMock;
    }

    /**
     * @return CartRestoreService
     */
    private function createBasketRestoreServiceExpectMethodCalls()
    {
        $basketRestoreServiceMock = $this->createMock(CartRestoreService::class);
        $basketRestoreServiceMock->expects(static::once())->method('getCartData')->willReturn([]);
        $basketRestoreServiceMock->expects(static::once())->method('restoreCart');

        return $basketRestoreServiceMock;
    }

    /**
     * @return SimpleBasketValidator
     */
    private function createBasketValidator()
    {
        $basketValidatorMock = $this->createMock(SimpleBasketValidator::class);
        $basketValidatorMock->method('validate')->willReturn(true);

        return $basketValidatorMock;
    }

    /**
     * @return RedirectDataBuilderFactoryInterface
     */
    private function createRedirectDataBuilderFactory()
    {
        $exceptionHandlerMock = $this->createMock(ExceptionHandlerService::class);
        $settingsServiceMock = $this->createMock(SettingsService::class);

        $redirectDataBuilderFactoryMock = $this->createMock(RedirectDataBuilderFactoryInterface::class);
        $redirectDataBuilderFactoryMock->method('createRedirectDataBuilder')->willReturn(
            new RedirectDataBuilder($exceptionHandlerMock, $settingsServiceMock)
        );

        return $redirectDataBuilderFactoryMock;
    }

    /**
     * @return OrderFactory
     */
    private function createOrderFactory()
    {
        $orderFactoryMock = $this->createMock(OrderFactory::class);
        $orderFactoryMock->method('createOrder')->willReturn($this->createPayPalOrder());

        return $orderFactoryMock;
    }

    /**
     * @return PayPalOrderParameterFacade
     */
    private function createPayPalOrderParameterFacade()
    {
        $payPalOrderParameterFacadeMock = $this->createMock(PayPalOrderParameterFacade::class);
        $payPalOrderParameterFacadeMock->method('createPayPalOrderParameter')->willReturn(
            new PayPalOrderParameter([], [], PaymentType::APM_GIROPAY, '', '')
        );

        return $payPalOrderParameterFacadeMock;
    }

    /**
     * @return DependencyProvider
     */
    private function createDependencyProvider()
    {
        $userData = require __DIR__ . '/../../../_fixtures/s_user_data.php';

        $sessionMock = $this->createMock(Enlight_Components_Session_Namespace::class);
        $sessionMock->method('get')->willReturn($userData);

        $dependencyProviderMock = $this->createMock(DependencyProvider::class);
        $dependencyProviderMock->method('getSession')->willReturn($sessionMock);

        return $dependencyProviderMock;
    }

    /**
     * @return Enlight_Controller_Request_RequestTestCase
     */
    private function createRequest()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('token', '3E630337S9748511R');
        $request->setParam('paypalOrderId', 'someId');

        return $request;
    }

    /**
     * @return Order
     */
    private function createPayPalOrder()
    {
        $amount = new Amount();
        $amount->setValue('347.89');
        $amount->setCurrencyCode('EUR');

        $payee = new Order\PurchaseUnit\Payee();
        $payee->setEmailAddress('test@business.example.com');

        $purchaseUnit = new Order\PurchaseUnit();
        $purchaseUnit->setAmount($amount);
        $purchaseUnit->setPayee($payee);

        $giroPay = new Order\PaymentSource\Giropay();
        $giroPay->setCountryCode('DE');
        $giroPay->setName('Max Mustermann');

        $paymentSource = new Order\PaymentSource();
        $paymentSource->setGiropay($giroPay);

        $order = new Order();
        $order->setId(self::TRANSACTION_ID);
        $order->setIntent('CAPTURE');
        $order->setCreateTime('2022-04-25T06:51:36Z');
        $order->setStatus('APPROVED');
        $order->setPurchaseUnits([$purchaseUnit]);
        $order->setPaymentSource($paymentSource);

        return $order;
    }
}
