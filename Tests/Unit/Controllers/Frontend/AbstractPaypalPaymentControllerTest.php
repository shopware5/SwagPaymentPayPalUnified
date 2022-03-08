<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Controllers\Frontend;

use Enlight_Controller_Request_RequestHttp;
use Enlight_Controller_Response_ResponseHttp;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Bridge\Config;
use Shopware\Components\DependencyInjection\Container;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacadeInterface;
use SwagPaymentPayPalUnified\Components\Services\DispatchValidation;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactoryInterface;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AbstractPaypalPaymentControllerTest extends TestCase
{
    /**
     * @var MockObject|DependencyProvider
     */
    protected $dependencyProvider;

    /**
     * @var MockObject|RedirectDataBuilderFactoryInterface
     */
    protected $redirectDataBuilderFactory;

    /**
     * @var MockObject|PaymentControllerHelper
     */
    protected $paymentControllerHelper;

    /**
     * @var MockObject|DispatchValidation
     */
    protected $dispatchValidator;

    /**
     * @var MockObject|PayPalOrderParameterFacadeInterface
     */
    protected $payPalOrderParameterFacade;

    /**
     * @var MockObject|OrderResource
     */
    protected $orderResource;

    /**
     * @var MockObject|OrderFactory
     */
    protected $orderFactory;

    /**
     * @var MockObject|SettingsServiceInterface
     */
    protected $settingsService;

    /**
     * @var MockObject|OrderDataService
     */
    protected $orderDataService;

    /**
     * @var MockObject|PaymentMethodProviderInterface
     */
    protected $paymentMethodProvider;

    /**
     * @var MockObject|ExceptionHandlerServiceInterface
     */
    protected $exceptionHandler;

    /**
     * @var MockObject|Config
     */
    protected $shopwareConfig;

    /**
     * @var MockObject|LoggerServiceInterface
     */
    protected $logger;

    /**
     * @var MockObject|Enlight_Controller_Request_RequestHttp
     */
    protected $request;

    /**
     * @var MockObject|Enlight_Controller_Response_ResponseHttp
     */
    protected $response;

    /**
     * @var MockObject|RedirectDataBuilder
     */
    protected $redirectDataBuilder;

    /**
     * @before
     *
     * @return void
     */
    public function init()
    {
        $this->dependencyProvider = static::createMock(DependencyProvider::class);
        $this->redirectDataBuilderFactory = static::createMock(RedirectDataBuilderFactoryInterface::class);
        $this->paymentControllerHelper = static::createMock(PaymentControllerHelper::class);
        $this->dispatchValidator = static::createMock(DispatchValidation::class);
        $this->payPalOrderParameterFacade = static::createMock(PayPalOrderParameterFacadeInterface::class);
        $this->orderResource = static::createMock(OrderResource::class);
        $this->orderFactory = static::createMock(OrderFactory::class);
        $this->settingsService = static::createMock(SettingsServiceInterface::class);
        $this->orderDataService = static::createMock(OrderDataService::class);
        $this->paymentMethodProvider = static::createMock(PaymentMethodProviderInterface::class);
        $this->exceptionHandler = static::createMock(ExceptionHandlerServiceInterface::class);
        $this->shopwareConfig = static::createMock(Config::class);
        $this->logger = static::createMock(LoggerServiceInterface::class);
        $this->request = static::createMock(Enlight_Controller_Request_RequestHttp::class);
        $this->response = static::createMock(Enlight_Controller_Response_ResponseHttp::class);

        $this->redirectDataBuilder = $this->getRedirectDataBuilder();
    }

    /**
     * @param string $paypalErrorCode
     * @param string $shopwareErrorCode
     *
     * @dataProvider cancelActionErrorCodeProvider
     *
     * @return void
     */
    public function testCancelActionUsesExpectedErrorCodes($paypalErrorCode, $shopwareErrorCode)
    {
        $this->prepareRedirectDataBuilderFactory($this->redirectDataBuilder);

        $this->givenThePaypalErrorCodeEquals($paypalErrorCode);
        $this->expectTheShopwareErrorCodeToBe($shopwareErrorCode);

        $this->getAbstractPaypalPaymentController()->cancelAction();
    }

    /**
     * @param string $checkoutType
     * @param string $paymentType
     *
     * @dataProvider getPaymentTypeCheckoutTypeProvider
     *
     * @return void
     */
    public function testGetPaymentTypeReturnsEarlyForCertainCheckoutTypes($checkoutType, $paymentType)
    {
        $this->givenTheCheckoutTypeEquals($checkoutType);

        static::assertSame(
            $paymentType,
            $this->getAbstractPaypalPaymentController()->getPaymentType(new Order())
        );
    }

    /**
     * @return array<string,array<string|int>>
     */
    public function cancelActionErrorCodeProvider()
    {
        return [
            'Unknown error code' => [
                'badcd139-2df3-4a12-89e0-0e0ec176843f',
                ErrorCodes::CANCELED_BY_USER,
            ],
            'Processing error' => [
                'processing_error',
                ErrorCodes::COMMUNICATION_FAILURE,
            ],
        ];
    }

    /**
     * @return array<string,array<string>>
     */
    public function getPaymentTypeCheckoutTypeProvider()
    {
        return [
            '"ACDC" checkout' => [
                'acdcCheckout',
                PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD,
            ],
            '"Smart Payment Buttons" checkout' => [
                'spbCheckout',
                PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2,
            ],
            '"In Context" checkout' => [
                'inContextCheckout',
                PaymentType::PAYPAL_CLASSIC_V2,
            ],
        ];
    }

    /**
     * @param string $errorCode
     *
     * @return void
     */
    protected function expectTheShopwareErrorCodeToBe($errorCode)
    {
        $this->redirectDataBuilder->expects(static::once())
            ->method('setCode')
            ->with($errorCode);
    }

    /**
     * @param string $errorCode
     *
     * @return void
     */
    protected function givenThePaypalErrorCodeEquals($errorCode)
    {
        $this->request->method('getParam')
            ->with('errorcode')
            ->willReturn($errorCode);
    }

    /**
     * @param string $checkoutType
     *
     * @return void
     */
    protected function givenTheCheckoutTypeEquals($checkoutType)
    {
        $this->request->method('getParam')
            ->will(static::returnValueMap([
                [$checkoutType, false, true],
                [static::anything(), false, false],
            ]));
    }

    /**
     * @return void
     */
    protected function prepareRedirectDataBuilderFactory(RedirectDataBuilder $redirectDataBuilder = null)
    {
        $this->redirectDataBuilderFactory->method('createRedirectDataBuilder')
            ->willReturn($redirectDataBuilder ?: $this->redirectDataBuilder);
    }

    /**
     * @return MockObject|RedirectDataBuilder
     */
    protected function getRedirectDataBuilder()
    {
        $redirectDataBuilder = static::createMock(RedirectDataBuilder::class);

        $redirectDataBuilder->method('setCode')
            ->will(static::returnSelf());
        $redirectDataBuilder->method('setException')
            ->will(static::returnSelf());
        $redirectDataBuilder->method('setRedirectToFinishAction')
            ->will(static::returnSelf());

        return $redirectDataBuilder;
    }

    /**
     * @return TestPaypalPaymentController
     */
    protected function getAbstractPaypalPaymentController(
        DependencyProvider $dependencyProvider = null,
        RedirectDataBuilderFactoryInterface $redirectDataBuilderFactory = null,
        PaymentControllerHelper $paymentControllerHelper = null,
        DispatchValidation $dispatchValidator = null,
        PayPalOrderParameterFacadeInterface $payPalOrderParameterFacade = null,
        OrderResource $orderResource = null,
        OrderFactory $orderFactory = null,
        SettingsServiceInterface $settingsService = null,
        OrderDataService $orderDataService = null,
        PaymentMethodProviderInterface $paymentMethodProvider = null,
        ExceptionHandlerServiceInterface $exceptionHandler = null,
        Config $shopwareConfig = null,
        LoggerServiceInterface $logger = null,
        Enlight_Controller_Request_RequestHttp $request = null,
        Enlight_Controller_Response_ResponseHttp $response = null
    ) {
        $container = static::createMock(Container::class);

        $container->method('get')
            ->will(static::returnValueMap([
                ['paypal_unified.dependency_provider', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $dependencyProvider ?: $this->dependencyProvider],
                ['paypal_unified.redirect_data_builder_factory', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $redirectDataBuilderFactory ?: $this->redirectDataBuilderFactory],
                ['paypal_unified.payment_controller_helper', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $paymentControllerHelper ?: $this->paymentControllerHelper],
                ['paypal_unified.dispatch_validation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $dispatchValidator ?: $this->dispatchValidator],
                ['paypal_unified.paypal_order_parameter_facade', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $payPalOrderParameterFacade ?: $this->payPalOrderParameterFacade],
                ['paypal_unified.v2.order_resource', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $orderResource ?: $this->orderResource],
                ['paypal_unified.order_factory', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $orderFactory ?: $this->orderFactory],
                ['paypal_unified.settings_service', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $settingsService ?: $this->settingsService],
                ['paypal_unified.order_data_service', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $orderDataService ?: $this->orderDataService],
                ['paypal_unified.payment_method_provider', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $paymentMethodProvider ?: $this->paymentMethodProvider],
                ['paypal_unified.exception_handler_service', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $exceptionHandler ?: $this->exceptionHandler],
                ['config', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $shopwareConfig ?: $this->shopwareConfig],
                ['paypal_unified.logger_service', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $logger ?: $this->logger],
            ]));

        $controller = \Enlight_Class::Instance(
            TestPaypalPaymentController::class,
            [
                $request ?: $this->request, // Setting request for Shopware <= v5.2
                $response ?: $this->response, // Setting response for Shopware <= v5.2
            ]
        );

        if (!$controller instanceof TestPaypalPaymentController) {
            throw new \UnexpectedValueException(sprintf('Instantiation of controller %s failed.', TestPaypalPaymentController::class));
        }

        $controller->setContainer($container);
        $controller->setRequest($request ?: $this->request); // Set request for Shopware > v5.2
        $controller->setResponse($response ?: $this->response); // Set response for Shopware > v5.2
        $controller->preDispatch();

        return $controller;
    }
}

class TestPaypalPaymentController extends AbstractPaypalPaymentController
{
    /**
     * @return int|string
     */
    public function getPaymentType(Order $order)
    {
        return parent::getPaymentType($order);
    }
}
