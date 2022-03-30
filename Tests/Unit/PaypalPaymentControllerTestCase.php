<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit;

use Enlight_Controller_Request_RequestHttp;
use Enlight_Controller_Response_ResponseHttp;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Bridge\Config;
use Shopware\Components\DependencyInjection\Container;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacadeInterface;
use SwagPaymentPayPalUnified\Components\Services\DispatchValidation;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketValidatorInterface;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactoryInterface;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaypalPaymentControllerTestCase extends TestCase
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
     * @var MockObject|PaymentStatusService
     */
    protected $paymentStatusService;

    /**
     * @var MockObject|LoggerServiceInterface
     */
    protected $logger;

    /**
     * @var MockObject|BasketValidatorInterface
     */
    protected $basketValidator;

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
        $this->paymentStatusService = static::createMock(PaymentStatusService::class);
        $this->logger = static::createMock(LoggerServiceInterface::class);
        $this->basketValidator = static::createMock(BasketValidatorInterface::class);
        $this->request = static::createMock(Enlight_Controller_Request_RequestHttp::class);
        $this->response = static::createMock(Enlight_Controller_Response_ResponseHttp::class);

        $this->redirectDataBuilder = $this->getRedirectDataBuilder();
    }

    /**
     * @return MockObject|RedirectDataBuilder
     */
    protected function getRedirectDataBuilder()
    {
        $mock = static::createMock(RedirectDataBuilder::class);

        $mock->method('setCode')
            ->will(static::returnSelf());
        $mock->method('setException')
            ->will(static::returnSelf());
        $mock->method('setRedirectToFinishAction')
            ->will(static::returnSelf());

        return $mock;
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
     * @template T of AbstractPaypalPaymentController
     *
     * @param class-string<T> $controllerClass
     *
     * @return T
     */
    protected function getController(
        $controllerClass,
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
        PaymentStatusService $paymentStatusService = null,
        LoggerServiceInterface $logger = null,
        BasketValidatorInterface $basketValidator = null,
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
                ['paypal_unified.payment_status_service', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $paymentStatusService ?: $this->paymentStatusService],
                ['paypal_unified.logger_service', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $logger ?: $this->logger],
                ['paypal_unified.simple_basket_validator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $basketValidator ?: $this->basketValidator],
            ]));

        $controller = \Enlight_Class::Instance(
            $controllerClass,
            [
                $request ?: $this->request, // Setting request for Shopware <= v5.2
                $response ?: $this->response, // Setting response for Shopware <= v5.2
            ]
        );

        if (!$controller instanceof $controllerClass) {
            throw new \UnexpectedValueException(sprintf('Instantiation of controller %s failed.', $controllerClass));
        }

        $controller->setContainer($container);
        $controller->setRequest($request ?: $this->request); // Set request for Shopware > v5.2
        $controller->setResponse($response ?: $this->response); // Set response for Shopware > v5.2
        $controller->preDispatch();

        return $controller;
    }
}
