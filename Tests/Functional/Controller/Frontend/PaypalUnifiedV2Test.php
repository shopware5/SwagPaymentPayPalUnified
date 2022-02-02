<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedV2.php';

use ArrayObject;
use Enlight_Class;
use Enlight_Components_Session_Namespace as ShopwareSession;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Components\BasketSignature\BasketPersister;
use Shopware\Components\BasketSignature\BasketSignatureGenerator;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\HttpClient\RequestException;
use Shopware_Components_Config;
use Shopware_Controllers_Frontend_PaypalUnifiedV2;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacadeInterface;
use SwagPaymentPayPalUnified\Components\Services\Common\CartPersister;
use SwagPaymentPayPalUnified\Components\Services\DispatchValidation;
use SwagPaymentPayPalUnified\Components\Services\LoggerService;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UnexpectedValueException;

class PaypalUnifiedV2Test extends TestCase
{
    /**
     * @param array|null $orderData
     * @param bool       $isValidDispatch
     * @param bool       $premiumShippingNoOrder
     * @param bool       $orderBuilderException
     * @param bool       $orderResourceCommunicationFailure
     * @param int        $expectedErrorCode
     *
     * @dataProvider orderDataProvider
     */
    public function testIndexErrorHandling(
        $orderData,
        $isValidDispatch,
        $premiumShippingNoOrder,
        $orderBuilderException,
        $orderResourceCommunicationFailure,
        $expectedErrorCode
    ) {
        $session = $this->createMock(ShopwareSession::class);
        $session->method('get')
            ->willReturnMap([
                ['expressData', null, null],
                ['sOrderVariables', null, $orderData],
            ]);

        $session->method('offsetGet')
            ->willReturnMap([
                ['sOrderVariables', \is_array($orderData) ? new ArrayObject($orderData) : null],
                ['sUserId', 1],
            ]);

        $dependencyProvider = $this->createConfiguredMock(DependencyProvider::class, [
            'getSession' => $session,
        ]);

        $redirectDataBuilder = $this->createMock(RedirectDataBuilder::class);
        $redirectDataBuilder->expects(static::atLeastOnce())
            ->method('setCode')
            ->with($expectedErrorCode)
            ->willReturn($redirectDataBuilder);
        $redirectDataBuilder->method('setException')
            ->willReturn($redirectDataBuilder);

        $redirectDataBuilderFactory = $this->createConfiguredMock(RedirectDataBuilderFactory::class, [
            'createRedirectDataBuilder' => $redirectDataBuilder,
        ]);

        $configService = $this->createMock(Shopware_Components_Config::class);
        $configService->method('get')
            ->willReturnMap([
                ['premiumShippingNoOrder', null, $premiumShippingNoOrder],
            ]);

        $orderFactory = $this->createMock(OrderFactory::class);

        if ($orderBuilderException) {
            $orderFactory->method('createOrder')
                ->willThrowException(new UnexpectedValueException());
        } else {
            $orderFactory->method('createOrder')
                ->willReturn($this->createMock(Order::class));
        }

        $orderResource = $this->createMock(OrderResource::class);

        if ($orderResourceCommunicationFailure) {
            $orderResource->method('create')
                ->willThrowException(new RequestException());
        }

        $paymentControllerHelper = $this->createConfiguredMock(PaymentControllerHelper::class, [
            'setGrossPriceFallback' => [],
        ]);

        $signatureGenerator = null;

        if (class_exists(BasketSignatureGenerator::class)) {
            $signatureGenerator = $this->createMock(BasketSignatureGenerator::class);
            $signatureGenerator->method('generateSignature')
                ->willReturn([]);
        }

        $basketPersister = null;

        if (class_exists(BasketPersister::class)) {
            $basketPersister = $this->createMock(BasketPersister::class);
        }

        $cartPersister = $this->createMock(CartPersister::class);
        $orderParameterFacade = $this->createConfiguredMock(PayPalOrderParameterFacadeInterface::class, [
            'createPayPalOrderParameter' => $this->createMock(PayPalOrderParameter::class),
        ]);

        $dispatchValidation = $this->createMock(DispatchValidation::class);
        $dispatchValidation->method('isInvalid')
            ->willReturn($isValidDispatch);

        $logger = $this->createMock(LoggerService::class);

        $container = $this->createMock(Container::class);
        $container->method('get')
            ->willReturnMap([
                ['paypal_unified.dependency_provider', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $dependencyProvider],
                ['paypal_unified.redirect_data_builder_factory', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $redirectDataBuilderFactory],
                ['config', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $configService],
                ['paypal_unified.v2.order_resource', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $orderResource],
                ['paypal_unified.payment_controller_helper', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $paymentControllerHelper],
                ['session', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $session],
                ['basket_signature_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $signatureGenerator],
                ['basket_persister', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $basketPersister],
                ['paypal_unified.common.cart_persister', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $cartPersister],
                ['paypal_unified.paypal_order_parameter_facade', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $orderParameterFacade],
                ['paypal_unified.dispatch_validation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $dispatchValidation],
                ['paypal_unified.order_factory', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $orderFactory],
                ['swag_payment_pay_pal_unified.logger', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $logger],
            ]);

        $controller = $this->getController($container);

        $controller->indexAction();
    }

    /**
     * @return Generator
     */
    public function orderDataProvider()
    {
        yield 'Empty order data should lead to ErrorCodes::NO_ORDER_TO_PROCESS' => [
            null,
            false,
            false,
            false,
            false,
            ErrorCodes::NO_ORDER_TO_PROCESS,
        ];

        yield 'Order data without dispatch should lead to ErrorCodes::NO_DISPATCH_FOR_ORDER' => [
            [],
            true,
            false,
            false,
            false,
            ErrorCodes::NO_DISPATCH_FOR_ORDER,
        ];

        yield 'Exception in OrderBuilder::getOrder should lead to ErrorCodes::UNKNOWN' => [
            [
                'sUserData' => [],
                'sBasket' => [],
            ],
            false,
            false,
            true,
            false,
            ErrorCodes::UNKNOWN,
        ];

        yield 'Exception during order creation request should lead to ErrorCodes::COMMUNICATION_FAILURE' => [
            [
                'sUserData' => [],
                'sBasket' => [],
            ],
            false,
            false,
            false,
            true,
            ErrorCodes::COMMUNICATION_FAILURE,
        ];
    }

    /**
     * @param Container $container
     */
    private function getController(Container $container = null)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        /** @var Shopware_Controllers_Frontend_PaypalUnifiedV2 $controller */
        $controller = Enlight_Class::Instance(
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            [$request, $response]
        );

        $controller->setRequest($request);
        $controller->setResponse($response);

        if ($container instanceof Container) {
            $controller->setContainer($container);
        } else {
            $controller->setContainer(Shopware()->Container());
        }

        $controller->preDispatch();

        return $controller;
    }
}
