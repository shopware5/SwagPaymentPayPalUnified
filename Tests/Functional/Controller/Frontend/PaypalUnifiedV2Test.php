<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedV2.php';

use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\HttpClient\RequestException;
use Shopware_Controllers_Frontend_PaypalUnifiedV2;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrderBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
        $session = static::createMock(SessionInterface::class);
        $session->method('get')
            ->will(static::returnValueMap([
                ['sOrderVariables', null, $orderData],
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

        $container = static::createMock(Container::class);
        $container->method('get')
            ->will(static::returnValueMap([
                ['paypal_unified.dependency_provider', Container::EXCEPTION_ON_INVALID_REFERENCE, $dependencyProvider],
                ['paypal_unified.redirect_data_builder_factory', Container::EXCEPTION_ON_INVALID_REFERENCE, $redirectDataBuilderFactory],
                ['config', Container::EXCEPTION_ON_INVALID_REFERENCE, $configService],
                ['paypal_unified.paypal_order_builder_service', Container::EXCEPTION_ON_INVALID_REFERENCE, $orderBuilderService],
                ['paypal_unified.v2.order_resource', Container::EXCEPTION_ON_INVALID_REFERENCE, $orderResource],
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
