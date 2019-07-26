<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Subscriber\ExpressCheckout as ExpressCheckoutSubscriber;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\ClientService;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\PaymentResourceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class ExpressCheckoutSubscriberTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

    public function test_construct()
    {
        $subscriber = $this->getSubscriber();

        static::assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $events = ExpressCheckoutSubscriber::getSubscribedEvents();

        static::assertCount(6, $events);

        static::assertSame('addExpressCheckoutButtonCart', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend']);
        static::assertCount(2, $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']);
        static::assertSame('addExpressCheckoutButtonDetail', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail']);
        static::assertSame('addExpressCheckoutButtonListing', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Listing']);
        static::assertSame('addExpressCheckoutButtonLogin', $events['Enlight_Controller_Action_PostDispatch_Frontend_Register']);
    }

    public function test_addExpressCheckoutButtonCart_return_payment_method_inactive()
    {
        $paymentMethodProvider = new PaymentMethodProvider(Shopware()->Container()->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);

        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, null),
        ]);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedUseInContext'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(true);
    }

    public function test_addExpressCheckoutButtonCart_return_unified_inactive()
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

    public function test_addExpressCheckoutButtonCart_return_ec_inactive()
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

    public function test_addExpressCheckoutButtonCart_return_wrongController()
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

    public function test_addExpressCheckoutButtonCart_return_wrongAction()
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

    public function test_addExpressCheckoutButtonCart_assigns_value_to_cart()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('cart');
        $request->setControllerName('checkout');
        $view->assign('sBasket', ['content' => 'foo']);

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertTrue($view->getAssign('paypalUnifiedModeSandbox'));
    }

    public function test_addExpressCheckoutButtonCart_assigns_value_to_ajax_cart()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('ajaxCart');
        $request->setControllerName('checkout');
        $view->assign('sBasket', ['content' => 'foo']);

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertTrue($view->getAssign('paypalUnifiedModeSandbox'));
    }

    public function test_addEcInfoOnConfirm_return_wrong_action()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('fake');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();
        $subscriber->addEcInfoOnConfirm($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedExpressPaymentId'));
    }

    public function test_addEcInfoOnConfirm_return_no_ec()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();
        $subscriber->addEcInfoOnConfirm($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedExpressPaymentId'));
    }

    public function test_addEcInfoOnConfirm_assigns_correct_values_on_confirm_action()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');
        $request->setParam('paymentId', 'TEST_PAYMENT_ID');
        $request->setParam('payerId', 'TEST_PAYER_ID');
        $request->setParam('expressCheckout', true);
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addEcInfoOnConfirm($enlightEventArgs);

        static::assertSame('TEST_PAYMENT_ID', $view->getAssign('paypalUnifiedExpressPaymentId'));
        static::assertSame('TEST_PAYER_ID', $view->getAssign('paypalUnifiedExpressPayerId'));
        static::assertTrue($view->getAssign('paypalUnifiedExpressCheckout'));
    }

    public function test_addPaymentInfoToRequest_return_wrong_action()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('fake');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();

        static::assertNull($subscriber->addPaymentInfoToRequest($enlightEventArgs));
    }

    public function test_addPaymentInfoToRequest_return_wrong_param()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();

        static::assertNull($subscriber->addPaymentInfoToRequest($enlightEventArgs));
    }

    public function test_addPaymentInfoToRequest_return_no_redirect()
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

        static::assertNull($subscriber->addPaymentInfoToRequest($enlightEventArgs));
    }

    public function test_addPaymentInfoToRequest_throws_exception()
    {
        $session = Shopware()->Session();
        $session->offsetSet('sOrderVariables', require __DIR__ . '/_fixtures/sOrderVariables.php');
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');
        $request->setParam('expressCheckout', true);

        $response = new \Enlight_Controller_Response_ResponseTestCase();
        $response->setHttpResponseCode(302);

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
            'request' => $request,
            'response' => $response,
        ]);

        $subscriber = $this->getSubscriber(true);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('patch exception');
        $subscriber->addPaymentInfoToRequest($enlightEventArgs);
    }

    public function test_addPaymentInfoToRequest()
    {
        $session = Shopware()->Session();
        $session->offsetSet('sOrderVariables', require __DIR__ . '/_fixtures/sOrderVariables.php');
        $view = new ViewMock(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');
        $request->setParam('expressCheckout', true);

        $response = new \Enlight_Controller_Response_ResponseTestCase();
        $response->setHttpResponseCode(302);

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
            'request' => $request,
            'response' => $response,
        ]);

        $subscriber = $this->getSubscriber();

        $subscriber->addPaymentInfoToRequest($enlightEventArgs);

        static::assertContains('/PaypalUnified/return/expressCheckout/1/paymentId//PayerID//basketId/', $response->getHeader('Location'));
        static::assertSame(302, $response->getHttpResponseCode());
    }

    public function test_addExpressCheckoutButtonDetail_return_payment_method_inactive()
    {
        $paymentMethodProvider = new PaymentMethodProvider(Shopware()->Container()->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);

        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(true);
    }

    public function test_addExpressCheckoutButtonDetail_return_unified_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function test_addExpressCheckoutButtonDetail_returns_because_ec_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function test_addExpressCheckoutButtonDetail_return_ec_detail_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function test_addExpressCheckoutButtonDetail_assigns_correct_values()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertTrue($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function test_addExpressCheckoutButtonListing_return_payment_method_inactive()
    {
        $paymentMethodProvider = new PaymentMethodProvider(Shopware()->Container()->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);

        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(true);
    }

    public function test_addExpressCheckoutButtonListing_return_unified_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function test_addExpressCheckoutButtonListing_returns_because_ec_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function test_addExpressCheckoutButtonListing_return_ec_detail_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
    }

    public function test_addExpressCheckoutButtonListing_assigns_correct_values()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, false, false, false, false, false, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertSame($view->getAssign('paypalUnifiedEcButtonStyleSize'), 'small');
        static::assertTrue($view->getAssign('paypalUnifiedEcListingActive'));
    }

    public function test_addExpressCheckoutButtonLogin_return_payment_method_inactive()
    {
        $paymentMethodProvider = new PaymentMethodProvider(Shopware()->Container()->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);

        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcLoginActive'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(true);
    }

    public function test_addExpressCheckoutButtonLogin_return_unified_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcLoginActive'));
    }

    public function test_addExpressCheckoutButtonLogin_returns_because_ec_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcLoginActive'));
    }

    public function test_addExpressCheckoutButtonLogin_return_ec_detail_inactive()
    {
        $view = new ViewMock(new \Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcLoginActive'));
    }

    public function test_addExpressCheckoutButtonLogin_assigns_correct_values()
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

    /**
     * @param bool $active
     * @param bool $ecCartActive
     * @param bool $ecDetailActive
     * @param bool $sandboxMode
     * @param bool $ecLoginActive
     * @param bool $ecOffCanvasActive
     */
    private function importSettings(
        $active = false,
        $ecCartActive = false,
        $ecDetailActive = false,
        $sandboxMode = false,
        $ecLoginActive = false,
        $ecOffCanvasActive = false,
        $ecListingActive = false
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
        ]);
    }

    /**
     * @param bool $usePaymentResourceMock
     *
     * @return ExpressCheckoutSubscriber
     */
    private function getSubscriber($usePaymentResourceMock = false)
    {
        Shopware()->Container()->set('paypal_unified.client_service', new ClientService());

        $paymentResource = Shopware()->Container()->get('paypal_unified.payment_resource');
        if ($usePaymentResourceMock) {
            $paymentResource = new PaymentResourceMock();
        }

        return new ExpressCheckoutSubscriber(
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('session'),
            $paymentResource,
            Shopware()->Container()->get('paypal_unified.payment_address_service'),
            Shopware()->Container()->get('paypal_unified.payment_builder_service'),
            Shopware()->Container()->get('paypal_unified.exception_handler_service'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.client_service'),
            Shopware()->Container()->get('paypal_unified.dependency_provider')
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
}
