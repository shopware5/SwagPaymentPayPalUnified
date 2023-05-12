<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware_Controllers_Frontend_PaypalUnifiedV2;
use Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout;
use Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacadeInterface;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\Validation\SimpleBasketValidator;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\AssertLocationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\LoggerMock;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class ControllerHandlePendingOrderTest extends PaypalPaymentControllerTestCase
{
    use AssertLocationTrait;
    use ContainerTrait;
    use ShopRegistrationTrait;

    const PAYPAL_ORDER_ID = 'anyPayPalOrderId';

    const EXPECTED_LOG_ENTRY = 'SwagPaymentPayPalUnified\\Controllers\\Frontend\\AbstractPaypalPaymentController::handlePendingOrder REDIRECT TO checkout/finish';

    /**
     * @before
     *
     * @return void
     */
    public function setSessionData()
    {
        $userData = require __DIR__ . '/_fixtures/getUser_result.php';
        $cartData = require __DIR__ . '/_fixtures/getBasket_result.php';

        $session = $this->getContainer()->get('session');
        $session->offsetSet(
            'sOrderVariables',
            [
                'sUserData' => $userData,
                'sBasket' => $cartData,
            ]
        );
    }

    /**
     * @after
     *
     * @return void
     */
    public function cleanUp()
    {
        $session = $this->getContainer()->get('session');

        if (method_exists($session, 'clear')) {
            $session->clear();
        } else {
            $session->offsetUnset('sUserId');
            $session->offsetUnset('sOrderVariables');
        }

        $this->getContainer()->get('dbal_connection')->delete('s_order', ['transactionID' => self::PAYPAL_ORDER_ID]);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param PaymentIntentV2::* $intent
     *
     * @return void
     */
    public function testPayPalUnifiedV2ControllerReturnActionCatchPendingException($intent)
    {
        $request = $this->createRequest();

        $response = new Enlight_Controller_Response_ResponseTestCase();

        $payPalOrder = $this->createPendingPayPalOrder($intent);

        $orderResourceMock = $this->createResourceMock($payPalOrder, $intent);

        $simpleBasketValidatorMock = $this->createSimpleBasketValidatorMock();

        $loggerMock = new LoggerMock();

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            [
                self::SERVICE_DEPENDENCY_PROVIDER => $this->getContainer()->get('paypal_unified.dependency_provider'),
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $simpleBasketValidatorMock,
                self::SERVICE_ORDER_PROPERTY_HELPER => $this->getContainer()->get('paypal_unified.order_property_helper'),
                self::SERVICE_LOGGER_SERVICE => $loggerMock,
            ],
            $request,
            $response
        );

        $controller->returnAction();

        $result = $loggerMock->getDebug();
        static::assertTrue(\array_key_exists(self::EXPECTED_LOG_ENTRY, $result));
    }

    /**
     * @dataProvider dataProvider
     *
     * @param PaymentIntentV2::* $intent
     *
     * @return void
     */
    public function testPaypalUnifiedV2ExpressCheckoutControllerExpressCheckoutFinishActionCatchPendingExceptionTestDataProvider(
        $intent
    ) {
        $request = $this->createRequest();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $payPalOrder = $this->createPendingPayPalOrder($intent);

        $orderResourceMock = $this->createResourceMock($payPalOrder, $intent);

        $simpleBasketValidatorMock = $this->createSimpleBasketValidatorMock();

        $payPalOrderParameterFacadeMock = $this->createPayPalOrderParameterFacadeMock();

        $orderFactoryMock = $this->createOrderFactoryMock($payPalOrder);

        $loggerMock = new LoggerMock();

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout::class,
            [
                self::SERVICE_DEPENDENCY_PROVIDER => $this->getContainer()->get('paypal_unified.dependency_provider'),
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $simpleBasketValidatorMock,
                self::SERVICE_ORDER_PROPERTY_HELPER => $this->getContainer()->get('paypal_unified.order_property_helper'),
                self::SERVICE_ORDER_PARAMETER_FACADE => $payPalOrderParameterFacadeMock,
                self::SERVICE_ORDER_FACTORY => $orderFactoryMock,
                self::SERVICE_LOGGER_SERVICE => $loggerMock,
            ],
            $request,
            $response
        );

        $controller->expressCheckoutFinishAction();

        $result = $loggerMock->getDebug();
        static::assertTrue(\array_key_exists(self::EXPECTED_LOG_ENTRY, $result));
    }

    /**
     * @dataProvider dataProvider
     *
     * @param PaymentIntentV2::* $intent
     *
     * @return void
     */
    public function testPaypalUnifiedV2AdvancedCreditDebitCardControllerCreateOrderActionCatchPendingExceptionTestDataProvider(
        $intent
    ) {
        $request = $this->createRequest();

        $response = new Enlight_Controller_Response_ResponseTestCase();

        $payPalOrder = $this->createPendingPayPalOrder($intent);

        $orderResourceMock = $this->createResourceMock($payPalOrder, $intent);

        $simpleBasketValidatorMock = $this->createSimpleBasketValidatorMock();

        $payPalOrderParameterFacadeMock = $this->createPayPalOrderParameterFacadeMock();

        $orderFactoryMock = $this->createOrderFactoryMock($payPalOrder);

        $loggerMock = new LoggerMock();

        $controller = $this->getController(
            Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard::class,
            [
                self::SERVICE_DEPENDENCY_PROVIDER => $this->getContainer()->get('paypal_unified.dependency_provider'),
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $simpleBasketValidatorMock,
                self::SERVICE_ORDER_PROPERTY_HELPER => $this->getContainer()->get('paypal_unified.order_property_helper'),
                self::SERVICE_ORDER_PARAMETER_FACADE => $payPalOrderParameterFacadeMock,
                self::SERVICE_ORDER_FACTORY => $orderFactoryMock,
                self::SERVICE_LOGGER_SERVICE => $loggerMock,
            ],
            $request,
            $response
        );

        $controller->captureAction();

        $result = $loggerMock->getDebug();
        static::assertTrue(\array_key_exists(self::EXPECTED_LOG_ENTRY, $result));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function dataProvider()
    {
        yield 'Intent is CAPTURE' => [
            PaymentIntentV2::CAPTURE,
        ];

        yield 'Intent is AUTHORIZE' => [
            PaymentIntentV2::AUTHORIZE,
        ];
    }

    /**
     * @param PaymentIntentV2::* $intent
     *
     * @return Order
     */
    private function createPendingPayPalOrder($intent)
    {
        $payment = new Payments();

        if ($intent === PaymentIntentV2::CAPTURE) {
            $capture = new Capture();
            $capture->setStatus(PaymentStatusV2::ORDER_CAPTURE_PENDING);
            $payment->setCaptures([$capture]);
        }

        if ($intent === PaymentIntentV2::AUTHORIZE) {
            $authorize = new Authorization();
            $authorize->setStatus(PaymentStatusV2::ORDER_AUTHORIZATION_PENDING);
            $payment->setAuthorizations([$authorize]);
        }

        $amount = new PurchaseUnit\Amount();
        $amount->setValue('100');

        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setPayments($payment);
        $purchaseUnit->setAmount($amount);

        $order = new Order();
        $order->setIntent($intent);
        $order->setPurchaseUnits([$purchaseUnit]);
        $order->setId(self::PAYPAL_ORDER_ID);

        return $order;
    }

    /**
     * @param PaymentType::* $paymentType
     *
     * @return PayPalOrderParameter
     */
    private function createPaypalOrderParameter($paymentType)
    {
        $userData = require __DIR__ . '/_fixtures/getUser_result.php';
        $cartData = require __DIR__ . '/_fixtures/getBasket_result.php';

        return new PayPalOrderParameter($userData, $cartData, $paymentType, null, null, 'anyOrderId');
    }

    /**
     * @return Enlight_Controller_Request_RequestTestCase
     */
    private function createRequest()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('token', self::PAYPAL_ORDER_ID);

        return $request;
    }

    /**
     * @param PaymentIntentV2::* $intent
     *
     * @return OrderResource&MockObject
     */
    private function createResourceMock(Order $payPalOrder, $intent)
    {
        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($payPalOrder);
        $orderResourceMock->method(strtolower($intent))->willReturn($payPalOrder);

        return $orderResourceMock;
    }

    /**
     * @return SimpleBasketValidator&MockObject
     */
    private function createSimpleBasketValidatorMock()
    {
        $simpleBasketValidatorMock = $this->createMock(SimpleBasketValidator::class);
        $simpleBasketValidatorMock->method('validate')->willReturn(true);

        return $simpleBasketValidatorMock;
    }

    /**
     * @return PayPalOrderParameterFacadeInterface&MockObject
     */
    private function createPayPalOrderParameterFacadeMock()
    {
        $payPalOrderParameterFacadeMock = $this->createMock(PayPalOrderParameterFacadeInterface::class);
        $payPalOrderParameterFacadeMock->method('createPayPalOrderParameter')
            ->willReturn(
                $this->createPaypalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2)
            );

        return $payPalOrderParameterFacadeMock;
    }

    /**
     * @return OrderFactory&MockObject
     */
    private function createOrderFactoryMock(Order $payPalOrder)
    {
        $orderFactoryMock = $this->createMock(OrderFactory::class);
        $orderFactoryMock->method('createOrder')->willReturn($payPalOrder);

        return $orderFactoryMock;
    }
}
