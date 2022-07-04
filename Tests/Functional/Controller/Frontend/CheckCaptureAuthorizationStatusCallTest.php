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
use PHPUnit\Framework\MockObject\MockObject;
use Shopware_Controllers_Frontend_PaypalUnifiedV2;
use Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout;
use Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacade;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\OrderPropertyHelper;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\Components\Services\Validation\SimpleBasketValidator;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
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
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedApm.php';
require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedV2.php';
require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedV2ExpressCheckout.php';
require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedV2PayUponInvoice.php';
require_once __DIR__ . '/../../../../Controllers/Widgets/PaypalUnifiedV2AdvancedCreditDebitCard.php';

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

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout::class,
            [
                self::SERVICE_ORDER_PARAMETER_FACADE => $payPalOrderParameterFacade,
                self::SERVICE_ORDER_FACTORY => $orderFactory,
                self::SERVICE_ORDER_RESOURCE => $orderResource,
                self::SERVICE_ORDER_PROPERTY_HELPER => $orderPropertyHelper,
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

        $orderResource = $this->createOrderResource($payPalOrder);
        $simpleBasketValidator = $this->createSimpleBasketValidator();
        $orderPropertyHelper = $this->createOrderPropertyHelperTestCase($payPalOrder);

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            [
                self::SERVICE_ORDER_RESOURCE => $orderResource,
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $simpleBasketValidator,
                self::SERVICE_ORDER_PROPERTY_HELPER => $orderPropertyHelper,
            ],
            $request
        );

        $controller->returnAction();
    }

    /**
     * @dataProvider liabilityShiftProvider
     *
     * @param ?string $liabilityShift
     *
     * @return void
     */
    public function testPaypalUnifiedV2AdvancedCreditDebitCardCaptureAction($liabilityShift)
    {
        $this->prepareSession();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('paypalOrderId', '123456');
        $request->setParam('liabilityShift', $liabilityShift);

        $payPalOrder = $this->createPayPalOrder(PaymentIntentV2::CAPTURE, PaymentStatusV2::ORDER_CAPTURE_COMPLETED);

        $simpleBasketValidator = $this->createSimpleBasketValidator();

        $dependencyProvider = $this->createMock(DependencyProvider::class);
        $dependencyProvider->method('getSession')->willReturn(
            $this->createMock(Enlight_Components_Session_Namespace::class)
        );

        $redirectDataBuilder = $this->getRedirectDataBuilder();

        if ($liabilityShift === 'POSSIBLE') {
            $orderResource = $this->createOrderResource($payPalOrder);
            $orderPropertyHelper = $this->createOrderPropertyHelperTestCase($payPalOrder);
        } else {
            $orderResource = $this->createMock(OrderResource::class);
            $orderPropertyHelper = $this->createMock(OrderPropertyHelper::class);

            $orderResource->expects(static::never())->method('get');
            $orderResource->expects(static::never())->method('capture');

            $orderPropertyHelper->expects(static::never())->method('getFirstCapture');

            $redirectDataBuilder->expects(static::once())->method('setCode')->with(ErrorCodes::THREE_D_SECURE_CHECK_FAILED);
        }

        $redirectDataBuilderFactory = $this->createConfiguredMock(RedirectDataBuilderFactory::class, [
            'createRedirectDataBuilder' => $redirectDataBuilder,
        ]);

        $controller = $this->getController(
            Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard::class,
            [
                self::SERVICE_ORDER_RESOURCE => $orderResource,
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $simpleBasketValidator,
                self::SERVICE_ORDER_PROPERTY_HELPER => $orderPropertyHelper,
                self::SERVICE_DEPENDENCY_PROVIDER => $dependencyProvider,
                self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY => $redirectDataBuilderFactory,
            ],
            $request
        );

        $controller->captureAction();
    }

    /**
     * @return array<string, array<?string>>
     */
    public function liabilityShiftProvider()
    {
        return [
            'Liability shift possible' => ['POSSIBLE'],
            'Liability shift empty' => [''],
            'Liability shift null' => [null],
        ];
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

        $order = new Order();
        $order->setIntent($intent);
        $order->setPurchaseUnits([$purchaseUnit]);

        return $order;
    }

    /**
     * @return OrderResource|MockObject
     */
    private function createOrderResource(Order $payPalOrder)
    {
        $orderResource = $this->createMock(OrderResource::class);
        $orderResource->expects(static::once())->method('get')->willReturn($payPalOrder);
        $orderResource->expects(static::once())->method('capture')->willReturn($payPalOrder);

        return $orderResource;
    }

    /**
     * @return SimpleBasketValidator|MockObject
     */
    private function createSimpleBasketValidator()
    {
        $simpleBasketValidator = $this->createMock(SimpleBasketValidator::class);
        $simpleBasketValidator->method('validate')->willReturn(true);

        return $simpleBasketValidator;
    }

    /**
     * @return OrderPropertyHelper|MockObject
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
