<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Components_Session_Namespace;
use Enlight_Controller_Request_RequestTestCase;
use Shopware_Controllers_Frontend_PaypalUnifiedV2;
use Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout;
use Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard;
use stdClass;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacade;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\OrderPropertyHelper;
use SwagPaymentPayPalUnified\Components\Services\Validation\SimpleBasketValidator;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\ConnectionMock;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class CheckCaptureAuthorizationStatusCallTest extends PaypalPaymentControllerTestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;

    /**
     * @return void
     */
    public function testPaypalUnifiedV2ExpressCheckoutExpressCheckoutFinishAction()
    {
        $this->prepareSession();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('paypalOrderId', '123456');

        $payPalOrderParameterFacade = $this->createMock(PayPalOrderParameterFacade::class);
        $payPalOrderParameterFacade->expects(static::once())->method('createPayPalOrderParameter')->willReturn(
            $this->createMock(PayPalOrderParameter::class)
        );

        $payPalOrder = $this->createPayPalOrder(PaymentIntentV2::CAPTURE, PaymentStatusV2::ORDER_CAPTURE_COMPLETED);

        $orderFactory = $this->createMock(OrderFactory::class);
        $orderFactory->expects(static::once())->method('createOrder')->willReturn($payPalOrder);
        $orderResource = $this->createOrderResource($payPalOrder);

        $orderPropertyHelper = $this->createOrderPropertyHelperTestCase($payPalOrder);

        $connectionMock = (new ConnectionMock())->createConnectionMock(1, ConnectionMock::METHOD_FETCH);

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout::class,
            [
                self::SERVICE_ORDER_PARAMETER_FACADE => $payPalOrderParameterFacade,
                self::SERVICE_ORDER_FACTORY => $orderFactory,
                self::SERVICE_ORDER_RESOURCE => $orderResource,
                self::SERVICE_ORDER_PROPERTY_HELPER => $orderPropertyHelper,
                self::SERVICE_DBAL_CONNECTION => $connectionMock,
            ],
            $request
        );

        $controller->expressCheckoutFinishAction();
    }

    /**
     * @return void
     */
    public function testPaypalUnifiedV2ReturnAction()
    {
        $this->prepareSession();

        $payPalOrder = $this->createPayPalOrder(PaymentIntentV2::CAPTURE, PaymentStatusV2::ORDER_CAPTURE_COMPLETED);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('token', '123456');
        $request->setParam('inContextCheckout', true);

        $orderResource = $this->createOrderResource($payPalOrder);
        $simpleBasketValidator = $this->createSimpleBasketValidator();
        $orderPropertyHelper = $this->createOrderPropertyHelperTestCase($payPalOrder);

        $connectionMock = (new ConnectionMock())->createConnectionMock(1, ConnectionMock::METHOD_FETCH);

        $sessionMock = $this->createMock(Enlight_Components_Session_Namespace::class);
        $sessionMock->method('offsetExists')->willReturn(true);
        $sessionMock->method('offsetGet')->willReturn('123456');

        $dependencyProviderMock = $this->createMock(DependencyProvider::class);
        $dependencyProviderMock->method('getSession')->willReturn($sessionMock);
        $dependencyProviderMock->method('getModule')->willReturn(new stdClass());

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            [
                self::SERVICE_ORDER_RESOURCE => $orderResource,
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $simpleBasketValidator,
                self::SERVICE_ORDER_PROPERTY_HELPER => $orderPropertyHelper,
                self::SERVICE_DBAL_CONNECTION => $connectionMock,
                self::SERVICE_DEPENDENCY_PROVIDER => $dependencyProviderMock,
            ],
            $request
        );

        $controller->returnAction();
    }

    /**
     * @return void
     */
    public function testPaypalUnifiedV2AdvancedCreditDebitCardCaptureAction()
    {
        $this->prepareSession();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('paypalOrderId', '123456');

        $payPalOrder = $this->createPayPalOrder(PaymentIntentV2::CAPTURE, PaymentStatusV2::ORDER_CAPTURE_COMPLETED);

        $orderResource = $this->createOrderResource($payPalOrder);
        $simpleBasketValidator = $this->createSimpleBasketValidator();
        $orderPropertyHelper = $this->createOrderPropertyHelperTestCase($payPalOrder);

        $dependencyProvider = $this->createMock(DependencyProvider::class);
        $dependencyProvider->method('getSession')->willReturn(
            $this->createMock(Enlight_Components_Session_Namespace::class)
        );

        $controller = $this->getController(
            Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard::class,
            [
                self::SERVICE_ORDER_RESOURCE => $orderResource,
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $simpleBasketValidator,
                self::SERVICE_ORDER_PROPERTY_HELPER => $orderPropertyHelper,
                self::SERVICE_DEPENDENCY_PROVIDER => $dependencyProvider,
            ],
            $request
        );

        $controller->captureAction();
    }

    /**
     * @return void
     */
    private function prepareSession()
    {
        $sOrderVariables = [
            'sUserData' => require __DIR__ . '/_fixtures/getUser_result.php',
            'sBasket' => require __DIR__ . '/_fixtures/getBasket_result.php',
        ];

        $this->getContainer()->get('session')->offsetSet('sOrderVariables', $sOrderVariables);
        $this->getContainer()->get('session')->offsetSet('sUserId', 1);
    }

    /**
     * @param PaymentIntentV2::* $intent
     * @param PaymentStatusV2::* $status
     *
     * @return Order
     */
    private function createPayPalOrder($intent, $status)
    {
        $capture = new Capture();
        $capture->setStatus($status);

        $authorization = new Authorization();
        $authorization->setStatus($status);

        $payments = new Payments();
        $payments->setAuthorizations([$authorization]);
        $payments->setCaptures([$capture]);

        $amount = new Amount();
        $amount->setValue('100');

        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setPayments($payments);
        $purchaseUnit->setAmount($amount);

        $authenticationResult = new AuthenticationResult();
        $authenticationResult->setLiabilityShift(AuthenticationResult::LIABILITY_SHIFT_POSSIBLE);

        $card = new Card();
        $card->setAuthenticationResult($authenticationResult);

        $paymentSource = new PaymentSource();
        $paymentSource->setCard($card);

        $order = new Order();
        $order->setIntent($intent);
        $order->setPurchaseUnits([$purchaseUnit]);
        $order->setPaymentSource($paymentSource);

        return $order;
    }

    /**
     * @return OrderResource
     */
    private function createOrderResource(Order $payPalOrder)
    {
        $orderResource = $this->createMock(OrderResource::class);
        $orderResource->expects(static::once())->method('get')->willReturn($payPalOrder);
        $orderResource->expects(static::once())->method('capture')->willReturn($payPalOrder);

        return $orderResource;
    }

    /**
     * @return SimpleBasketValidator
     */
    private function createSimpleBasketValidator()
    {
        $simpleBasketValidator = $this->createMock(SimpleBasketValidator::class);
        $simpleBasketValidator->method('validate')->willReturn(true);

        return $simpleBasketValidator;
    }

    /**
     * @return OrderPropertyHelper
     */
    private function createOrderPropertyHelperTestCase(Order $payPalOrder)
    {
        $orderPropertyHelperOriginal = $this->getContainer()->get(self::SERVICE_ORDER_PROPERTY_HELPER);
        $orderPropertyHelper = $this->createMock(OrderPropertyHelper::class);
        $orderPropertyHelper->expects(static::once())->method('getFirstCapture')->willReturn(
            $orderPropertyHelperOriginal->getFirstCapture($payPalOrder)
        );

        return $orderPropertyHelper;
    }
}
