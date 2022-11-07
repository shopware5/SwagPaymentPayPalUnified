<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Class;
use Enlight_Components_Session_Namespace;
use Enlight_Controller_Request_RequestHttp;
use Enlight_Controller_Response_ResponseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopware\Components\DependencyInjection\Container;
use Shopware_Controllers_Frontend_PaypalUnified;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\OrderNumberService;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\Components\Services\Validation\SimpleBasketValidator;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnified.php';

class PaypalUnifiedReturnActionTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use ShopRegistrationTrait;

    const ORDERNUMBER = '1234567';

    /**
     * @after
     *
     * @return void
     */
    public function resetSession()
    {
        $this->getContainer()->get('session')->offsetUnset('sOrderVariables');
    }

    /**
     * @return void
     */
    public function testReturnActionShouldApplyCorrectPaymentType()
    {
        $sBasket = require __DIR__ . '/_fixtures/getBasket_result.php';
        $sUserData = require __DIR__ . '/_fixtures/getUser_result.php';

        $this->getContainer()->get('session')->offsetSet('sOrderVariables', ['sBasket' => $sBasket, 'sUserData' => $sUserData]);
        $controller = $this->createController();

        $controller->returnAction();
    }

    /**
     * @return Shopware_Controllers_Frontend_PaypalUnified
     */
    private function createController()
    {
        $request = $this->createRequest();
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $container = $this->createContainer();

        $controller = Enlight_Class::Instance(Shopware_Controllers_Frontend_PaypalUnified::class, [$request, $response]);
        static::assertInstanceOf(Shopware_Controllers_Frontend_PaypalUnified::class, $controller);
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->setContainer($container);

        $controller->preDispatch();

        $reflectionClassController = new ReflectionClass(Shopware_Controllers_Frontend_PaypalUnified::class);

        $paymentResource = $this->createPaymentResource();
        $reflectionPropertyPaymentResource = $reflectionClassController->getProperty('paymentResource');
        $reflectionPropertyPaymentResource->setAccessible(true);
        $reflectionPropertyPaymentResource->setValue($controller, $paymentResource);

        $settingsService = $this->createSettingsService();
        $reflectionPropertySettingsService = $reflectionClassController->getProperty('settingsService');
        $reflectionPropertySettingsService->setAccessible(true);
        $reflectionPropertySettingsService->setValue($controller, $settingsService);

        $dependencyProvider = $this->createDependencyProvider();
        $reflectionPropertyDependencyProvider = $reflectionClassController->getProperty('dependencyProvider');
        $reflectionPropertyDependencyProvider->setAccessible(true);
        $reflectionPropertyDependencyProvider->setValue($controller, $dependencyProvider);

        return $controller;
    }

    /**
     * @return Enlight_Controller_Request_RequestHttp
     */
    private function createRequest()
    {
        $request = $this->createMock(Enlight_Controller_Request_RequestHttp::class);
        $request->method('getParam')->willReturnMap([
            ['paymentId', 1],
            ['basketId', 'plus'],
            ['plus', true],
            ['expressCheckout', false],
            ['spbCheckout', false],
            ['PayerID', 1],
        ]);

        return $request;
    }

    /**
     * @return PaymentResource
     */
    private function createPaymentResource()
    {
        $paymentResult = require __DIR__ . '/_fixtures/payment_result.php';
        $executeResult = require __DIR__ . '/_fixtures/execute_result.php';

        $paymentResource = $this->createMock(PaymentResource::class);
        $paymentResource->method('get')->willReturn($paymentResult);
        $paymentResource->method('execute')->willReturn($executeResult);

        return $paymentResource;
    }

    /**
     * @return Container
     */
    private function createContainer()
    {
        $simpleBasketValidator = $this->createSimpleBasketValidator();
        $orderDataService = $this->createOrderDataService();
        $orderNumberService = $this->createOrderNumberServiceMock();

        $container = $this->createMock(Container::class);
        $container->expects(static::once())->method('initialized')->willReturn(false);
        $container->method('get')->willReturnMap([
            ['paypal_unified.simple_basket_validator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $simpleBasketValidator],
            ['paypal_unified.order_data_service', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $orderDataService],
            ['paypal_unified.order_number_service', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $orderNumberService],
        ]);

        return $container;
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
     * @return SettingsService
     */
    private function createSettingsService()
    {
        $settingsService = $this->createMock(SettingsService::class);
        $settingsService->method('get')->willReturnMap([
            ['active', SettingsTable::PLUS, true],
        ]);

        return $settingsService;
    }

    /**
     * @return OrderDataService
     */
    private function createOrderDataService()
    {
        $orderDataService = $this->createMock(OrderDataService::class);
        $orderDataService->method('applyTransactionId')->willReturn(true);
        $orderDataService->expects(static::once())->method('applyPaymentTypeAttribute')->with(self::ORDERNUMBER, PaymentType::PAYPAL_PLUS);

        return $orderDataService;
    }

    /**
     * @return DependencyProvider
     */
    private function createDependencyProvider()
    {
        $session = $this->createMock(Enlight_Components_Session_Namespace::class);

        $dependencyProvider = $this->createMock(DependencyProvider::class);
        $dependencyProvider->expects(static::once())->method('getSession')->willReturn($session);

        return $dependencyProvider;
    }

    /**
     * @return OrderNumberService&MockObject
     */
    private function createOrderNumberServiceMock()
    {
        $orderNumberService = $this->createMock(OrderNumberService::class);
        $orderNumberService->method('getOrderNumber')->willReturn(self::ORDERNUMBER);
        $orderNumberService->expects(static::once())->method('releaseOrderNumber');

        return $orderNumberService;
    }
}
