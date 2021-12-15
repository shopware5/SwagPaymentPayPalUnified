<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedV2.php';

use Enlight_Components_Session_Namespace as ShopwareSession;
use PHPUnit\Framework\TestCase;
use Shopware\Components\BasketSignature\BasketPersister;
use Shopware\Components\BasketSignature\BasketSignatureGenerator;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\HttpClient\RequestException;
use Shopware_Controllers_Frontend_PaypalUnifiedV2;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacadeInterface;
use SwagPaymentPayPalUnified\Components\Services\Common\CartPersister;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrderBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaypalUnifiedV2Test extends TestCase
{
    /**
     * @param array|null $orderData
     * @param bool       $premiumShippingNoOrder
     * @param bool       $orderBuilderException
     * @param bool       $orderResourceCommunicationFailure
     * @param int        $expectedErrorCode
     *
     * @dataProvider orderDataProvider
     */
    public function testIndexErrorHandling(
        $orderData,
        $premiumShippingNoOrder,
        $orderBuilderException,
        $orderResourceCommunicationFailure,
        $expectedErrorCode
    ) {
        $session = static::createMock(ShopwareSession::class);
        $session->method('get')
            ->will(static::returnValueMap([
                ['expressData', null, null],
                ['sOrderVariables', null, $orderData],
            ]));

        $session->method('offsetGet')
            ->will(static::returnValueMap([
                ['sOrderVariables', \is_array($orderData) ? new \ArrayObject($orderData) : null],
                ['sUserId', 1],
            ]));

        $dependencyProvider = static::createConfiguredMock(DependencyProvider::class, [
            'getSession' => $session,
        ]);

        $redirectDataBuilder = static::createMock(RedirectDataBuilder::class);
        $redirectDataBuilder->expects(static::atLeastOnce())
            ->method('setCode')
            ->with($expectedErrorCode)
            ->willReturn($redirectDataBuilder);
        $redirectDataBuilder->method('setException')
            ->willReturn($redirectDataBuilder);

        $redirectDataBuilderFactory = static::createConfiguredMock(RedirectDataBuilderFactory::class, [
            'createRedirectDataBuilder' => $redirectDataBuilder,
        ]);

        $configService = static::createMock(\Shopware_Components_Config::class);
        $configService->method('get')
            ->will(static::returnValueMap([
                ['premiumShippingNoOrder', null, $premiumShippingNoOrder],
            ]));

        $orderBuilderService = static::createMock(PayPalOrderBuilderService::class);

        if ($orderBuilderException) {
            $orderBuilderService->method('getOrder')
                ->willThrowException(new \Exception());
        } else {
            $orderBuilderService->method('getOrder')
                ->willReturn(static::createMock(Order::class));
        }

        $orderResource = static::createMock(OrderResource::class);

        if ($orderResourceCommunicationFailure) {
            $orderResource->method('create')
                ->willThrowException(new RequestException());
        }

        $paymentControllerHelper = static::createConfiguredMock(PaymentControllerHelper::class, [
            'setGrossPriceFallback' => [],
        ]);

        $signatureGenerator = null;

        if (\class_exists('Shopware\Components\BasketSignature\BasketSignatureGenerator')) {
            $signatureGenerator = static::createMock(BasketSignatureGenerator::class);
            $signatureGenerator->method('generateSignature')
                ->willReturn([]);
        }

        $basketPersister = null;

        if (\class_exists('Shopware\Components\BasketSignature\BasketPersister')) {
            $basketPersister = static::createMock(BasketPersister::class);
        }

        $cartPersister = static::createMock(CartPersister::class);
        $orderParameterFacade = static::createConfiguredMock(PayPalOrderParameterFacadeInterface::class, [
            'createPayPalOrderParameter' => static::createMock(PayPalOrderParameter::class),
        ]);

        $container = static::createMock(Container::class);
        $container->method('get')
            ->will(static::returnValueMap([
                ['paypal_unified.dependency_provider', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $dependencyProvider],
                ['paypal_unified.redirect_data_builder_factory', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $redirectDataBuilderFactory],
                ['config', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $configService],
                ['paypal_unified.paypal_order_builder_service', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $orderBuilderService],
                ['paypal_unified.v2.order_resource', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $orderResource],
                ['paypal_unified.payment_controller_helper', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $paymentControllerHelper],
                ['session', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $session],
                ['basket_signature_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $signatureGenerator],
                ['basket_persister', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $basketPersister],
                ['paypal_unified.common.cart_persister', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $cartPersister],
                ['paypal_unified.paypal_order_parameter_facade', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $orderParameterFacade],
            ]));

        $controller = $this->getController($container);

        $controller->indexAction();
    }

    /**
     * @return \Generator
     */
    public function orderDataProvider()
    {
        yield 'Empty order data should lead to ErrorCodes::NO_ORDER_TO_PROCESS' => [
            null,
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
            ErrorCodes::NO_DISPATCH_FOR_ORDER,
        ];

        yield 'Exception in OrderBuilder::getOrder should lead to ErrorCodes::UNKNOWN' => [
            [
                'sUserData' => [],
                'sBasket' => [],
            ],
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
            true,
            ErrorCodes::COMMUNICATION_FAILURE,
        ];
    }

    /**
     * @param Container $container
     */
    private function getController(Container $container = null)
    {
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $response = new \Enlight_Controller_Response_ResponseTestCase();

        /** @var Shopware_Controllers_Frontend_PaypalUnifiedV2 $controller */
        $controller = \Enlight_Class::Instance(
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
