<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\EsdProductChecker;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\Subscriber\ExpressCheckout as ExpressCheckoutSubscriber;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\ClientService;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\LoggerMock;
use SwagPaymentPayPalUnified\Tests\Mocks\PaymentResourceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class ExpressCheckoutSubscriberTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

    /**
     * @var PaymentResource|PaymentResourceMock
     */
    private $paymentResource;

    /**
     * @var LoggerMock
     */
    private $loggerMock;

    public function testConstruct()
    {
        $subscriber = $this->getSubscriber();

        static::assertNotNull($subscriber);
    }

    public function testGetSubscribedEvents()
    {
        $events = ExpressCheckoutSubscriber::getSubscribedEvents();

        static::assertCount(6, $events);

        static::assertSame('addExpressCheckoutButtonCart', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend']);
        static::assertTrue(\is_array($events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']));
        static::assertCount(1, $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']);
        static::assertSame('addExpressCheckoutButtonDetail', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail']);
        static::assertSame('addExpressCheckoutButtonListing', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Listing']);
        static::assertSame('addExpressCheckoutButtonLogin', $events['Enlight_Controller_Action_PostDispatch_Frontend_Register']);
    }

    public function testAddExpressCheckoutButtonCartReturnPaymentMethodInactive()
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);

        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, null),
        ]);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedUseInContext'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);
    }

    public function testAddExpressCheckoutButtonCartReturnUnifiedInactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, null),
        ]);

        $this->importSettings(false, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedUseInContext'));
    }

    public function testAddExpressCheckoutButtonCartReturnEcInactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, null),
            'request' => $request,
        ]);

        $this->importSettings(true, false, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedUseInContext'));
    }

    public function testAddExpressCheckoutButtonCartReturnWrongController()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('detail');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedUseInContext'));
    }

    public function testAddExpressCheckoutButtonCartReturnWrongAction()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('checkout');
        $request->setActionName('fake');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedUseInContext'));
    }

    public function testAddExpressCheckoutButtonCartAssignsValueToCart()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('cart');
        $request->setControllerName('checkout');
        $view->assign('sBasket', ['content' => [[]]]);

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertTrue($view->getAssign('paypalUnifiedModeSandbox'));
    }

    public function testAddExpressCheckoutButtonCartAssignsValueToAjaxCart()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('ajaxCart');
        $request->setControllerName('checkout');
        $view->assign('sBasket', ['content' => [[]]]);

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertTrue($view->getAssign('paypalUnifiedModeSandbox'));
    }

    public function testAddExpressCheckoutButtonCartShouldReturnBecauseEsdProductIsInBasket()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber(
            false,
            static::createConfiguredMock(EsdProductChecker::class, [
                'checkForEsdProducts' => true,
            ])
        );
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcCartActive'));
        static::assertNull($view->getAssign('paypalUnifiedModeSandbox'));
        static::assertNull($view->getAssign('paypalUnifiedEcOffCanvasActive'));
        static::assertNull($view->getAssign('paypalUnifiedUseInContext'));
    }

    public function testAddEcInfoOnConfirmReturnWrongAction()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('fake');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressOrderInfoOnConfirm($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedExpressPaymentId'));
    }

    public function testAddEcInfoOnConfirmReturnNoEc()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressOrderInfoOnConfirm($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedExpressPaymentId'));
    }

    public function testAddEcInfoOnConfirmAssignsCorrectValuesOnConfirmAction()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');
        $request->setParam('orderId', 'TEST_PAYMENT_ID');
        $request->setParam('payerId', 'TEST_PAYER_ID');
        $request->setParam('expressCheckout', true);

        $view->assign('sBasket', ['content' => [['articleID' => 2]]]);

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressOrderInfoOnConfirm($enlightEventArgs);

        static::assertSame('TEST_PAYMENT_ID', $view->getAssign('paypalUnifiedExpressOrderId'));
        static::assertSame('TEST_PAYER_ID', $view->getAssign('paypalUnifiedExpressPayerId'));
        static::assertTrue($view->getAssign('paypalUnifiedExpressCheckout'));
    }

    public function testAddEcInfoOnConfirmShouldReturnBecauseEsdProductIsInBasket()
    {
        $basket = Shopware()->Modules()->Basket();

        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');
        $request->setParam('orderId', 'TEST_PAYMENT_ID');
        $request->setParam('payerId', 'TEST_PAYER_ID');
        $request->setParam('expressCheckout', true);
        Shopware()->Container()->get('front')->setRequest($request);
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $view->assign('sBasket', $basket->sGetBasket());

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber(
            false,
            static::createConfiguredMock(EsdProductChecker::class, [
                'checkForEsdProducts' => true,
            ])
        );
        $subscriber->addExpressOrderInfoOnConfirm($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedExpressPaymentId'));
        static::assertNull($view->getAssign('paypalUnifiedExpressPayerId'));
        static::assertNull($view->getAssign('paypalUnifiedExpressCheckout'));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAddPaymentInfoToRequestReturnWrongAction()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('fake');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();

        $subscriber->addExpressOrderInfoOnConfirm($enlightEventArgs);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAddPaymentInfoToRequestReturnWrongParam()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();

        $subscriber->addExpressOrderInfoOnConfirm($enlightEventArgs);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAddPaymentInfoToRequestReturnNoRedirect()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');
        $request->setParam('expressCheckout', true);

        $response = new \Enlight_Controller_Response_ResponseTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
            'response' => $response,
        ]);

        $subscriber = $this->getSubscriber();

        $subscriber->addExpressOrderInfoOnConfirm($enlightEventArgs);
    }

    public function testAddExpressCheckoutButtonDetailReturnPaymentMethodInactive()
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);

        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);
    }

    public function testAddExpressCheckoutButtonDetailReturnUnifiedInactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function testAddExpressCheckoutButtonDetailReturnsBecauseEcInactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function testAddExpressCheckoutButtonDetailReturnEcDetailInactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function testAddExpressCheckoutButtonDetailAssignsCorrectValues()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        Shopware()->Container()->get('session')->offsetUnset('sUserId');

        $this->importSettings(true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertTrue($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function testAddExpressCheckoutButtonDetailShouldReturnBecauseEsdProduct()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $view->assign('sArticle', ['esd' => true]);
        $enlightEventArgs = $this->createEventArgs($view);
        $enlightEventArgs->getSubject()->setRequest(new \Enlight_Controller_Request_RequestHttp());
        Shopware()->Container()->get('session')->offsetUnset('sUserId');

        $this->importSettings(true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function testAddExpressCheckoutButtonListingReturnPaymentMethodInactive()
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);

        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);
    }

    public function testAddExpressCheckoutButtonListingReturnUnifiedInactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function testAddExpressCheckoutButtonListingReturnsBecauseEcInactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function testAddExpressCheckoutButtonListingReturnEcDetailInactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function testAddExpressCheckoutButtonListingAssignsCorrectValues()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, false, false, false, false, false, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertSame($view->getAssign('paypalUnifiedEcButtonStyleSize'), 'small');
        static::assertTrue($view->getAssign('paypalUnifiedEcListingActive'));
    }

    public function testAddExpressCheckoutButtonListingAssignsEsdProductNumbers()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);
        $enlightEventArgs->getSubject()->Request()->setParam('sCategory', 10);

        $this->importSettings(true, false, false, false, false, false, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertSame('["SW10196"]', $view->getAssign('paypalUnifiedEsdProducts'));
    }

    public function testAddExpressCheckoutButtonLoginReturnPaymentMethodInactive()
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);

        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcLoginActive'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);
    }

    public function testAddExpressCheckoutButtonLoginReturnUnifiedInactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcLoginActive'));
    }

    public function testAddExpressCheckoutButtonLoginReturnsBecauseEcInactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcLoginActive'));
    }

    public function testAddExpressCheckoutButtonLoginReturnEcDetailInactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcLoginActive'));
    }

    public function testAddExpressCheckoutButtonLoginAssignsCorrectValues()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setParam('sTarget', 'checkout');
        $request->setParam('sTargetAction', 'confirm');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);
        $enlightEventArgs->set('request', $request);

        $this->importSettings(true, true, true, false, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertTrue($view->getAssign('paypalUnifiedEcLoginActive'));
    }

    public function testIsUserLoggedInShouldBeTrue()
    {
        Shopware()->Container()->get('session')->offsetSet('sUserId', 100);

        $reflectionMethod = (new \ReflectionClass(ExpressCheckoutSubscriber::class))->getMethod('isUserLoggedIn');
        $reflectionMethod->setAccessible(true);

        $subscriber = $this->getSubscriber();

        $result = $reflectionMethod->invoke($subscriber);

        static::assertTrue($result);
    }

    public function testIsUserLoggedInShouldBeFalse()
    {
        Shopware()->Container()->get('session')->offsetUnset('sUserId');

        $reflectionMethod = (new \ReflectionClass(ExpressCheckoutSubscriber::class))->getMethod('isUserLoggedIn');
        $reflectionMethod->setAccessible(true);

        $subscriber = $this->getSubscriber();

        $result = $reflectionMethod->invoke($subscriber);

        static::assertFalse($result);
    }

    /**
     * @param bool $active
     * @param bool $ecCartActive
     * @param bool $ecDetailActive
     * @param bool $sandboxMode
     * @param bool $ecLoginActive
     * @param bool $ecOffCanvasActive
     * @param bool $ecListingActive
     * @param bool $ecSubmitCart
     */
    private function importSettings(
        $active = false,
        $ecCartActive = false,
        $ecDetailActive = false,
        $sandboxMode = false,
        $ecLoginActive = false,
        $ecOffCanvasActive = false,
        $ecListingActive = false,
        $ecSubmitCart = false
    ) {
        $this->insertGeneralSettingsFromArray([
            'active' => $active,
            'shopId' => 1,
            'sandbox' => $sandboxMode,
        ]);

        $this->insertExpressCheckoutSettingsFromArray([
            'cartActive' => $ecCartActive,
            'detailActive' => $ecDetailActive,
            'listingActive' => $ecListingActive,
            'loginActive' => $ecLoginActive,
            'offCanvasActive' => $ecOffCanvasActive,
            'submitCart' => $ecSubmitCart,
        ]);
    }

    /**
     * @param bool                   $usePaymentResourceMock
     * @param EsdProductChecker|null $esdProductChecker
     *
     * @throws \Exception
     *
     * @return ExpressCheckoutSubscriber
     */
    private function getSubscriber($usePaymentResourceMock = false, $esdProductChecker = null)
    {
        Shopware()->Container()->set('paypal_unified.client_service', new ClientService());

        $this->paymentResource = Shopware()->Container()->get('paypal_unified.payment_resource');
        $this->loggerMock = new LoggerMock();

        if ($usePaymentResourceMock) {
            $this->paymentResource = new PaymentResourceMock();
        }

        if (!$esdProductChecker instanceof EsdProductChecker) {
            $esdProductChecker = Shopware()->Container()->get(EsdProductChecker::class);
        }

        return new ExpressCheckoutSubscriber(
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('session'),
            $this->paymentResource,
            Shopware()->Container()->get('paypal_unified.payment_address_service'),
            Shopware()->Container()->get('paypal_unified.payment_builder_service'),
            new ExceptionHandlerService($this->loggerMock),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.client_service'),
            Shopware()->Container()->get('paypal_unified.dependency_provider'),
            $esdProductChecker,
            Shopware()->Container()->get('paypal_unified.payment_method_provider')
        );
    }

    /**
     * @return \Enlight_Controller_ActionEventArgs
     */
    private function createEventArgs(ViewMock $view)
    {
        return new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController(new \Enlight_Controller_Request_RequestTestCase(), $view),
        ]);
    }

    private function getPaymentMethodProvider()
    {
        return new PaymentMethodProvider(
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('models')
        );
    }
}
