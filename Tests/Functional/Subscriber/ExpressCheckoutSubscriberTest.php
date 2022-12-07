<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Components_Session_Namespace;
use Enlight_Controller_ActionEventArgs;
use Enlight_Controller_Request_RequestHttp;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\EsdProductChecker;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\Models\Settings\ExpressCheckout;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\Subscriber\ExpressCheckout as ExpressCheckoutSubscriber;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\ClientService;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class ExpressCheckoutSubscriberTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use ShopRegistrationTrait;

    /**
     * @return void
     */
    public function testConstruct()
    {
        $subscriber = $this->getSubscriber();

        static::assertNotNull($subscriber);
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonCartReturnPaymentMethodInactive()
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $view->assign('sBasket', ['content' => [['articleID' => 2]]]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $this->importSettings();
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonCartReturnUnifiedInactive()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $view->assign('sBasket', ['content' => [['articleID' => 2]]]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $this->importSettings(false, true, true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonCartReturnEcInactive()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $view->assign('sBasket', ['content' => [['articleID' => 2]]]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->importSettings(true, false, true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonCartReturnWrongController()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('detail');
        $view->assign('sBasket', ['content' => [['articleID' => 2]]]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonCartReturnWrongAction()
    {
        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->getContainer()->get('session');
        if (method_exists($session, 'clear')) {
            $session->clear();
        } else {
            $session->unsetAll();
        }

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('checkout');
        $request->setActionName('fake');
        $view->assign('sBasket', ['content' => [['articleID' => 2]]]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonCartAssignsValueToCart()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('cart');
        $request->setControllerName('checkout');
        $view->assign('sBasket', ['content' => [['articleID' => 2]]]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $shop = $this->getContainer()->get('models')->getRepository(Shop::class)->getActiveDefault();
        $basket = $this->getContainer()->get('modules')->getModule('sBasket');

        $dependencyProviderMock = $this->createMock(DependencyProvider::class);
        $dependencyProviderMock->method('getShop')->willReturn($shop);
        $dependencyProviderMock->method('getModule')->willReturnMap([
            ['sBasket', $basket],
        ]);

        $generalSettings = new General();
        $generalSettings->setActive(true);
        $generalSettings->setSandbox(true);
        $generalSettings->setSandboxClientId('thisIsATestClientId');

        $expressSettings = new ExpressCheckout();
        $expressSettings->setLoginActive(false);

        $settingsServiceMock = $this->createMock(SettingsService::class);
        $settingsServiceMock->method('getSettings')->willReturnMap([
            [null, SettingsTable::GENERAL, $generalSettings],
            [null, SettingsTable::EXPRESS_CHECKOUT, $expressSettings],
        ]);
        $settingsServiceMock->method('get')->willReturn(true);

        $subscriber = $this->getSubscriber(null, $dependencyProviderMock, $settingsServiceMock);
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNotNull($enlightEventArgs->getSubject()->View()->getAssign('paypalUnifiedClientId'));
        static::assertTrue($enlightEventArgs->getSubject()->View()->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonCartAssignsValueToCartShowPayLaterShouldBeFalse()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('cart');
        $request->setControllerName('checkout');
        $view->assign('sBasket', ['content' => [['articleID' => 2]]]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $shop = $this->getContainer()->get('models')->getRepository(Shop::class)->getActiveDefault();
        $basket = $this->getContainer()->get('modules')->getModule('sBasket');

        $dependencyProviderMock = $this->createMock(DependencyProvider::class);
        $dependencyProviderMock->method('getShop')->willReturn($shop);
        $dependencyProviderMock->method('getModule')->willReturnMap([
            ['sBasket', $basket],
        ]);

        $generalSettings = new General();
        $generalSettings->setActive(true);
        $generalSettings->setSandbox(true);
        $generalSettings->setSandboxClientId('thisIsATestClientId');

        $expressSettings = new ExpressCheckout();
        $expressSettings->setLoginActive(false);

        $settingsServiceMock = $this->createMock(SettingsService::class);
        $settingsServiceMock->method('getSettings')->willReturnMap([
            [null, SettingsTable::GENERAL, $generalSettings],
            [null, SettingsTable::EXPRESS_CHECKOUT, $expressSettings],
        ]);
        $settingsServiceMock->method('get')->willReturn(false);

        $subscriber = $this->getSubscriber(null, $dependencyProviderMock, $settingsServiceMock);
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNotNull($enlightEventArgs->getSubject()->View()->getAssign('paypalUnifiedClientId'));
        static::assertFalse($enlightEventArgs->getSubject()->View()->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonCartAssignsValueToAjaxCart()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('ajaxCart');
        $request->setControllerName('checkout');
        $view->assign('sBasket', ['content' => [['articleID' => 2]]]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertTrue($view->getAssign('paypalUnifiedEcCartActive'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonCartShouldReturnBecauseEsdProductIsInBasket()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $view->assign('sBasket', ['content' => [['articleID' => 2]]]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber(
            static::createConfiguredMock(EsdProductChecker::class, [
                'checkForEsdProducts' => true,
            ])
        );
        $subscriber->addExpressCheckoutButtonCart($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcCartActive'));
        static::assertNull($view->getAssign('paypalUnifiedEcOffCanvasActive'));
    }

    /**
     * @return void
     */
    public function testAddEcInfoOnConfirmReturnWrongAction()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('fake');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressOrderInfoOnConfirm($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedExpressPaymentId'));
    }

    /**
     * @return void
     */
    public function testAddEcInfoOnConfirmReturnNoEc()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressOrderInfoOnConfirm($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedExpressPaymentId'));
    }

    /**
     * @return void
     */
    public function testAddEcInfoOnConfirmAssignsCorrectValuesOnConfirmAction()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');
        $request->setParam('paypalOrderId', 'TEST_PAYMENT_ID');
        $request->setParam('payerId', 'TEST_PAYER_ID');
        $request->setParam('expressCheckout', true);

        $view->assign('sBasket', ['content' => [['articleID' => 2]]]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressOrderInfoOnConfirm($enlightEventArgs);

        static::assertSame('TEST_PAYMENT_ID', $view->getAssign('paypalUnifiedExpressOrderId'));
        static::assertSame('TEST_PAYER_ID', $view->getAssign('paypalUnifiedExpressPayerId'));
        static::assertTrue($view->getAssign('paypalUnifiedExpressCheckout'));
    }

    /**
     * @return void
     */
    public function testAddEcInfoOnConfirmShouldReturnBecauseEsdProductIsInBasket()
    {
        $basket = Shopware()->Modules()->Basket();

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');
        $request->setParam('paypalOrderId', 'TEST_PAYMENT_ID');
        $request->setParam('payerId', 'TEST_PAYER_ID');
        $request->setParam('expressCheckout', true);
        $this->getContainer()->get('front')->setRequest($request);
        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $view->assign('sBasket', $basket->sGetBasket());

        $this->importSettings(true, true, true, true);

        $subscriber = $this->getSubscriber(
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
     *
     * @return void
     */
    public function testAddPaymentInfoToRequestReturnWrongAction()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('fake');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();

        $subscriber->addExpressOrderInfoOnConfirm($enlightEventArgs);
    }

    /**
     * @doesNotPerformAssertions
     *
     * @return void
     */
    public function testAddPaymentInfoToRequestReturnWrongParam()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();

        $subscriber->addExpressOrderInfoOnConfirm($enlightEventArgs);
    }

    /**
     * @doesNotPerformAssertions
     *
     * @return void
     */
    public function testAddPaymentInfoToRequestReturnNoRedirect()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');
        $request->setParam('expressCheckout', true);

        $response = new Enlight_Controller_Response_ResponseTestCase();

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
            'response' => $response,
        ]);

        $subscriber = $this->getSubscriber();

        $subscriber->addExpressOrderInfoOnConfirm($enlightEventArgs);
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonDetailReturnPaymentMethodInactive()
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);

        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonDetailReturnUnifiedInactive()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonDetailReturnsBecauseEcInactive()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonDetailReturnEcDetailInactive()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonDetailAssignsCorrectValues()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->getContainer()->get('session')->offsetUnset('sUserId');

        $this->importSettings(true, true, true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertTrue($view->getAssign('paypalUnifiedEcDetailActive'));
        static::assertTrue((bool) $view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonDetailShouldReturnBecauseEsdProduct()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sArticle', ['esd' => true]);
        $enlightEventArgs = $this->createEventArgs($view);
        $enlightEventArgs->getSubject()->setRequest(new Enlight_Controller_Request_RequestHttp());
        $this->getContainer()->get('session')->offsetUnset('sUserId');

        $this->importSettings(true, true, true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonDetail($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonListingReturnPaymentMethodInactive()
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);

        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonListingReturnUnifiedInactive()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonListingReturnsBecauseEcInactive()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonListingReturnEcDetailInactive()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcDetailActive'));
        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonListingAssignsCorrectValues()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, false, false, false, false, false, true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertSame('small', $view->getAssign('paypalUnifiedEcButtonStyleSize'));
        static::assertTrue($view->getAssign('paypalUnifiedEcListingActive'));
        static::assertTrue((bool) $view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonListingAssignsCorrectValuesShowPayLaterShouldBeFalse()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, false, false, false, false, false, true);
        $this->insertInstallmentsSettingsFromArray(['show_pay_later_express' => 0]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertSame('small', $view->getAssign('paypalUnifiedEcButtonStyleSize'));
        static::assertTrue($view->getAssign('paypalUnifiedEcListingActive'));
        static::assertFalse((bool) $view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonListingAssignsEsdProductNumbers()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);
        $enlightEventArgs->getSubject()->Request()->setParam('sCategory', 10);

        $this->importSettings(true, false, false, false, false, false, true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertSame('["SW10196"]', $view->getAssign('paypalUnifiedEsdProducts'));
        static::assertTrue((bool) $view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonListingAssignsEsdProductNumbersShowPayLaterShouldBeFalse()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);
        $enlightEventArgs->getSubject()->Request()->setParam('sCategory', 10);

        $this->importSettings(true, false, false, false, false, false, true);
        $this->insertInstallmentsSettingsFromArray(['show_pay_later_express' => 0]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonListing($enlightEventArgs);

        static::assertSame('["SW10196"]', $view->getAssign('paypalUnifiedEsdProducts'));
        static::assertFalse((bool) $view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonLoginReturnPaymentMethodInactive()
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);

        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcLoginActive'));
        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonLoginReturnUnifiedInactive()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings();
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcLoginActive'));
        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonLoginReturnsBecauseEcInactive()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcLoginActive'));
        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonLoginReturnEcDetailInactive()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $enlightEventArgs = $this->createEventArgs($view);

        $this->importSettings(true, true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedEcLoginActive'));
        static::assertNull($view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testAddExpressCheckoutButtonLoginAssignsCorrectValues()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('sTarget', 'checkout');
        $request->setParam('sTargetAction', 'confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);
        $enlightEventArgs->set('request', $request);

        $this->importSettings(true, true, true, false, true);
        $this->insertInstallmentsSettingsFromArray([]);

        $subscriber = $this->getSubscriber();
        $subscriber->addExpressCheckoutButtonLogin($enlightEventArgs);

        static::assertTrue($view->getAssign('paypalUnifiedEcLoginActive'));
        static::assertTrue((bool) $view->getAssign('paypalUnifiedShowPayLaterExpress'));
    }

    /**
     * @return void
     */
    public function testIsUserLoggedInShouldBeTrue()
    {
        $this->getContainer()->get('session')->offsetSet('sUserId', 100);

        $reflectionMethod = (new ReflectionClass(ExpressCheckoutSubscriber::class))->getMethod('isUserLoggedIn');
        $reflectionMethod->setAccessible(true);

        $subscriber = $this->getSubscriber();

        $result = $reflectionMethod->invoke($subscriber);

        static::assertTrue($result);
    }

    /**
     * @return void
     */
    public function testIsUserLoggedInShouldBeFalse()
    {
        $this->getContainer()->get('session')->offsetUnset('sUserId');

        $reflectionMethod = (new ReflectionClass(ExpressCheckoutSubscriber::class))->getMethod('isUserLoggedIn');
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
     *
     * @return void
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
        $generalSettings = [
            'active' => $active,
            'shopId' => 1,
            'sandbox' => $sandboxMode,
            'brandName' => 'DefaultTestBrandName',
        ];

        if ($sandboxMode) {
            $generalSettings['sandboxClientId'] = '0f3ee59b-3346-421c-be93-ca77921237dc';
        } else {
            $generalSettings['clientId'] = '35931479-2ab5-495c-977f-d0a75717e65e';
        }

        $this->insertGeneralSettingsFromArray($generalSettings);

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
     * @param EsdProductChecker|null  $esdProductChecker
     * @param DependencyProvider|null $dependencyProvider
     * @param SettingsService|null    $settingsService
     *
     * @return ExpressCheckoutSubscriber
     */
    private function getSubscriber($esdProductChecker = null, $dependencyProvider = null, $settingsService = null)
    {
        $this->getContainer()->set('paypal_unified.client_service', new ClientService());

        if (!$settingsService instanceof SettingsService) {
            $settingsService = $this->getContainer()->get('paypal_unified.settings_service');
        }

        if (!$esdProductChecker instanceof EsdProductChecker) {
            $esdProductChecker = $this->getContainer()->get(EsdProductChecker::class);
        }

        if (!$dependencyProvider instanceof DependencyProvider) {
            $dependencyProvider = $this->getContainer()->get('paypal_unified.dependency_provider');
        }

        return new ExpressCheckoutSubscriber(
            $settingsService,
            $this->getContainer()->get('session'),
            $dependencyProvider,
            $esdProductChecker,
            $this->getContainer()->get('paypal_unified.payment_method_provider'),
            $this->getContainer()->get('paypal_unified.button_locale_service')
        );
    }

    /**
     * @return Enlight_Controller_ActionEventArgs
     */
    private function createEventArgs(ViewMock $view)
    {
        return new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController(new Enlight_Controller_Request_RequestTestCase(), $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);
    }

    /**
     * @return PaymentMethodProvider
     */
    private function getPaymentMethodProvider()
    {
        return new PaymentMethodProvider(
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('models')
        );
    }
}
