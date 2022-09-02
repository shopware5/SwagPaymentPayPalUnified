<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit;

use Doctrine\DBAL\Connection;
use Enlight_Class;
use Enlight_Controller_Request_RequestHttp;
use Enlight_Controller_Response_ResponseHttp;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Components\BasketSignature\BasketPersister;
use Shopware\Components\Cart\BasketHelperInterface;
use Shopware\Components\Cart\ProportionalTaxCalculatorInterface;
use Shopware\Components\DependencyInjection\Bridge\Config;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\Backend\ShopRegistrationService;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacadeInterface;
use SwagPaymentPayPalUnified\Components\Services\CartRestoreService;
use SwagPaymentPayPalUnified\Components\Services\DispatchValidation;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\OrderPropertyHelper;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Components\Services\Plus\PaymentInstructionService;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketValidatorInterface;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactoryInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\AuthorizationResource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\CaptureResource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use UnexpectedValueException;

class PaypalPaymentControllerTestCase extends TestCase
{
    use ContainerTrait;

    const SERVICE_DEPENDENCY_PROVIDER = 'paypal_unified.dependency_provider';
    const SERVICE_REDIRECT_DATA_BUILDER = 'paypal_unified.redirect_data_builder';
    const SERVICE_REDIRECT_DATA_BUILDER_FACTORY = 'paypal_unified.redirect_data_builder_factory';
    const SERVICE_PAYMENT_CONTROLLER_HELPER = 'paypal_unified.payment_controller_helper';
    const SERVICE_DISPATCH_VALIDATION = 'paypal_unified.dispatch_validation';
    const SERVICE_ORDER_PARAMETER_FACADE = 'paypal_unified.paypal_order_parameter_facade';
    const SERVICE_ORDER_RESOURCE = 'paypal_unified.v2.order_resource';
    const SERVICE_ORDER_FACTORY = 'paypal_unified.order_factory';
    const SERVICE_SETTINGS_SERVICE = 'paypal_unified.settings_service';
    const SERVICE_ORDER_DATA_SERVICE = 'paypal_unified.order_data_service';
    const SERVICE_PAYMENT_METHOD_PROVIDER = 'paypal_unified.payment_method_provider';
    const SERVICE_EXCEPTION_HANDLER_SERVICE = 'paypal_unified.exception_handler_service';
    const SERVICE_SHOPWARE_CONFIG = 'config';
    const SERVICE_PAYMENT_STATUS_SERVICE = 'paypal_unified.payment_status_service';
    const SERVICE_LOGGER_SERVICE = 'paypal_unified.logger_service';
    const SERVICE_SIMPLE_BASKET_VALIDATOR = 'paypal_unified.simple_basket_validator';
    const SERVICE_CART_RESTORE_SERVICE = 'paypal_unified.cart_restore_service';
    const SERVICE_BASKET_PERSISTER = 'basket_persister';
    const SERVICE_ORDER_PROPERTY_HELPER = 'paypal_unified.order_property_helper';
    const SERVICE_SHOP_REGISTRATION_SERVICE = 'paypal_unified.backend.shop_registration_service';
    const SERVICE_AUTHORIZATION_RESOURCE = 'paypal_unified.v2.authorization_resource';
    const SERVICE_CAPTURE_RESOURCE = 'paypal_unified.v2.capture_resource';
    const SERVICE_PAYMENT_INSTRUCTION_SERVICE = 'paypal_unified.pay_upon_invoice_instruction_service';
    const SERVICE_SHOP = 'shop';
    const SERVICE_BASKET_HELPER_CLASS = BasketHelperInterface::class;
    const SERVICE_BASKET_HELPER_ID = 'shopware.cart.basket_helper';
    const SERVICE_PROPORTIONAL_TAX_CALCULATOR = 'shopware.cart.proportional_tax_calculator';
    const SERVICE_DBAL_CONNECTION = 'dbal_connection';

    /**
     * @var MockObject|Enlight_Controller_Request_RequestHttp
     */
    protected $request;

    /**
     * @var MockObject|Enlight_Controller_Response_ResponseHttp
     */
    protected $response;

    /**
     * @var array<self::SERVICE_*,MockObject>
     */
    protected $injections;

    /**
     * @var OrderPropertyHelper|null
     */
    protected $orderPropertyHelper;

    /**
     * @param string|null      $name
     * @param array<int,mixed> $data
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->injections[self::SERVICE_DEPENDENCY_PROVIDER] = $this->createMock(DependencyProvider::class);
        $this->injections[self::SERVICE_REDIRECT_DATA_BUILDER] = $this->getRedirectDataBuilder();
        $this->injections[self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY] = $this->createMock(RedirectDataBuilderFactoryInterface::class);
        $this->injections[self::SERVICE_PAYMENT_CONTROLLER_HELPER] = $this->createMock(PaymentControllerHelper::class);
        $this->injections[self::SERVICE_DISPATCH_VALIDATION] = $this->createMock(DispatchValidation::class);
        $this->injections[self::SERVICE_ORDER_PARAMETER_FACADE] = $this->createMock(PayPalOrderParameterFacadeInterface::class);
        $this->injections[self::SERVICE_ORDER_RESOURCE] = $this->createMock(OrderResource::class);
        $this->injections[self::SERVICE_ORDER_FACTORY] = $this->createMock(OrderFactory::class);
        $this->injections[self::SERVICE_SETTINGS_SERVICE] = $this->createMock(SettingsServiceInterface::class);
        $this->injections[self::SERVICE_ORDER_DATA_SERVICE] = $this->createMock(OrderDataService::class);
        $this->injections[self::SERVICE_PAYMENT_METHOD_PROVIDER] = $this->createMock(PaymentMethodProviderInterface::class);
        $this->injections[self::SERVICE_EXCEPTION_HANDLER_SERVICE] = $this->createMock(ExceptionHandlerServiceInterface::class);
        $this->injections[self::SERVICE_SHOPWARE_CONFIG] = $this->createMock(Config::class);
        $this->injections[self::SERVICE_PAYMENT_STATUS_SERVICE] = $this->createMock(PaymentStatusService::class);
        $this->injections[self::SERVICE_LOGGER_SERVICE] = $this->createMock(LoggerServiceInterface::class);
        $this->injections[self::SERVICE_SIMPLE_BASKET_VALIDATOR] = $this->createMock(BasketValidatorInterface::class);
        $this->injections[self::SERVICE_CART_RESTORE_SERVICE] = $this->createMock(CartRestoreService::class);
        $this->injections[self::SERVICE_ORDER_PROPERTY_HELPER] = $this->createOrderPropertyHelper();
        $this->injections[self::SERVICE_SHOP_REGISTRATION_SERVICE] = $this->createMock(ShopRegistrationService::class);
        $this->injections[self::SERVICE_AUTHORIZATION_RESOURCE] = $this->createMock(AuthorizationResource::class);
        $this->injections[self::SERVICE_CAPTURE_RESOURCE] = $this->createMock(CaptureResource::class);
        $this->injections[self::SERVICE_PAYMENT_INSTRUCTION_SERVICE] = $this->createMock(PaymentInstructionService::class);
        $this->injections[self::SERVICE_SHOP] = $this->createMock(Shop::class);
        $this->injections[self::SERVICE_DBAL_CONNECTION] = $this->createMock(Connection::class);

        if (class_exists(BasketPersister::class)) {
            $this->injections[self::SERVICE_BASKET_PERSISTER] = $this->createMock(BasketPersister::class);
        }

        if (interface_exists(BasketHelperInterface::class)) {
            $basketHelper = $this->createMock(BasketHelperInterface::class);
            $this->injections[self::SERVICE_BASKET_HELPER_CLASS] = $basketHelper;
            $this->injections[self::SERVICE_BASKET_HELPER_ID] = $basketHelper;
        }

        if (interface_exists(ProportionalTaxCalculatorInterface::class)) {
            $this->injections[self::SERVICE_PROPORTIONAL_TAX_CALCULATOR] = $this->createMock(ProportionalTaxCalculatorInterface::class);
        }

        $this->request = $this->createMock(Enlight_Controller_Request_RequestHttp::class);
        $this->response = $this->createMock(Enlight_Controller_Response_ResponseHttp::class);

        $this->prepareRequestStack();
    }

    /**
     * @param self::SERVICE_* $serviceKey
     *
     * @return MockObject
     */
    protected function getMockedService($serviceKey)
    {
        return $this->injections[$serviceKey];
    }

    /**
     * @return MockObject|RedirectDataBuilder
     */
    protected function getRedirectDataBuilder()
    {
        $mock = $this->createMock(RedirectDataBuilder::class);

        $mock->method('setCode')
            ->willReturnSelf();
        $mock->method('setException')
            ->willReturnSelf();
        $mock->method('setRedirectToFinishAction')
            ->willReturnSelf();

        return $mock;
    }

    /**
     * @return void
     */
    protected function prepareRedirectDataBuilderFactory(RedirectDataBuilder $redirectDataBuilder = null)
    {
        $redirectDataBuilderFactoryMock = $this->getMockedService(self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY);

        $redirectDataBuilderFactoryMock->method('createRedirectDataBuilder')
            ->willReturn($redirectDataBuilder ?: $this->getMockedService(self::SERVICE_REDIRECT_DATA_BUILDER));
    }

    /**
     * @template T of \SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController|\Enlight_Controller_Action
     *
     * @param class-string<T>               $controllerClass
     * @param array<self::SERVICE_*,object> $services
     *
     * @return T
     */
    protected function getController(
        $controllerClass,
        array $services,
        Enlight_Controller_Request_RequestHttp $request = null,
        Enlight_Controller_Response_ResponseHttp $response = null,
        Enlight_View_Default $view = null
    ) {
        $container = $this->createMock(Container::class);

        $returnMap = [];
        foreach ($this->injections as $serviceId => $service) {
            if (isset($services[$serviceId])) {
                $returnMap[] = [$serviceId, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $services[$serviceId]];
                continue;
            }

            $returnMap[] = [$serviceId, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $service];
        }

        $container->method('get')
            ->willReturnMap($returnMap);

        $controller = Enlight_Class::Instance(
            $controllerClass,
            [
                $request ?: $this->request, // Setting request for Shopware <= v5.2
                $response ?: $this->response, // Setting response for Shopware <= v5.2
            ]
        );

        if (!$controller instanceof $controllerClass) {
            throw new UnexpectedValueException(sprintf('Instantiation of controller %s failed.', $controllerClass));
        }

        if ($view === null) {
            $view = new Enlight_View_Default(new Enlight_Template_Manager());
        }

        $controller->setContainer($container);
        $controller->setRequest($request ?: $this->request); // Set request for Shopware > v5.2
        $controller->setResponse($response ?: $this->response); // Set response for Shopware > v5.2
        $controller->setView($view);
        $controller->preDispatch();

        return $controller;
    }

    /**
     * @return void
     */
    protected function prepareRequestStack()
    {
        $requestStack = $this->getContainer()->get('request_stack', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        if ($requestStack instanceof RequestStack) {
            $requestStack->push($this->request);
        }
        $this->getContainer()->get('front')->setRequest($this->request);
    }

    /**
     * @return MockObject
     */
    private function createOrderPropertyHelper()
    {
        $capture = new Capture();
        $capture->setStatus(PaymentStatusV2::ORDER_CAPTURE_COMPLETED);

        $authorization = new Authorization();
        $authorization->setStatus(PaymentStatusV2::ORDER_AUTHORIZATION_CREATED);

        $payments = new Payments();
        $payments->setCaptures([$capture]);
        $payments->setAuthorizations([$authorization]);

        $orderPaymentHelperMock = $this->createMock(OrderPropertyHelper::class);
        $orderPaymentHelperMock->method('getFirstCapture')->willReturn($capture);
        $orderPaymentHelperMock->method('getAuthorization')->willReturn($authorization);
        $orderPaymentHelperMock->method('getPayments')->willReturn($payments);

        return $orderPaymentHelperMock;
    }
}
