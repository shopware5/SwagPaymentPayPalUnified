<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Template_Manager;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Subscriber\Frontend;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class FrontendSubscriberTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

    public function test_can_be_created()
    {
        $subscriber = $this->getSubscriber();
        static::assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents_has_correct_events()
    {
        $events = Frontend::getSubscribedEvents();
        static::assertCount(4, $events);
        static::assertSame('onCollectJavascript', $events['Theme_Compiler_Collect_Plugin_Javascript']);
        static::assertSame('onPostDispatchSecure', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend']);
        static::assertSame('onLoadAjaxListing', $events['Enlight_Controller_Action_PreDispatch_Widgets_Listing']);
        static::assertSame('onCollectTemplateDir', $events['Theme_Inheritance_Template_Directories_Collected']);
    }

    public function test_onCollectJavascript()
    {
        $javascripts = $this->getSubscriber()->onCollectJavascript();

        foreach ($javascripts as $script) {
            static::assertFileExists($script);
        }

        static::assertCount(9, $javascripts);
    }

    public function test_onPostDistpatchSecure_without_any_setttings()
    {
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $result = $subscriber->onPostDispatchSecure($enlightEventArgs);

        static::assertNull($result);
    }

    public function test_onPostDispatchSecure_return_setting_inactive()
    {
        $subscriber = $this->getSubscriber();
        $this->createTestSettings(false);

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchSecure($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedShowLogo'));
    }

    public function test_onPostDispatchSecure_payment_method_inactive()
    {
        $paymentMethodProvider = new PaymentMethodProvider(Shopware()->Container()->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);
        $subscriber = $this->getSubscriber();
        $this->createTestSettings();

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchSecure($enlightEventArgs);

        static::assertFalse($view->getAssign('paypalUnifiedShowLogo'));

        $paymentMethodProvider->setPaymentMethodActiveFlag(true);
    }

    public function test_onPostDispatchSecure_assigns_variables_to_view()
    {
        $subscriber = $this->getSubscriber();
        $this->createTestSettings();

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchSecure($enlightEventArgs);

        static::assertTrue((bool) $view->getAssign('paypalUnifiedShowLogo'));
    }

    public function test_onCollectTemplateDir()
    {
        $subscriber = $this->getSubscriber();
        $returnValue = [];

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([]);

        $enlightEventArgs->setReturn($returnValue);

        $subscriber->onCollectTemplateDir($enlightEventArgs);
        $returnValue = $enlightEventArgs->getReturn();

        static::assertDirectoryExists($returnValue[0]);
    }

    public function test_onPostDispatchSecure_shouldAssignFalseToView()
    {
        Shopware()->Front()->setRequest(new \Enlight_Controller_Request_RequestHttp());

        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->createController();
        $controller->Request()->setParam('sArticle', 248);

        $eventArgs = new \Enlight_Controller_ActionEventArgs();
        $eventArgs->set('subject', $controller);

        $this->getSubscriber()->onPostDispatchSecure($eventArgs);

        $result = $controller->View()->getAssign('paypalIsNotAllowed');

        static::assertFalse($result);
    }

    public function test_onPostDispatchSecure_shoudAssignDataToView_shouldBeTrue()
    {
        Shopware()->Front()->setRequest(new \Enlight_Controller_Request_RequestHttp());

        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->createController();
        $controller->Request()->setParam('sArticle', 178);

        $eventArgs = new \Enlight_Controller_ActionEventArgs();
        $eventArgs->set('subject', $controller);

        $this->getSubscriber()->onPostDispatchSecure($eventArgs);

        $result = $controller->View()->getAssign('paypalIsNotAllowed');

        static::assertTrue($result);
    }

    public function test_onPostDispatchSecure_shoudAssignDataToView_shouldBeFalse()
    {
        Shopware()->Front()->setRequest(new \Enlight_Controller_Request_RequestHttp());

        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->createController();
        $controller->Request()->setParam('sArticle', 112);

        $eventArgs = new \Enlight_Controller_ActionEventArgs();
        $eventArgs->set('subject', $controller);

        $this->getSubscriber()->onPostDispatchSecure($eventArgs);

        $result = $controller->View()->getAssign('paypalIsNotAllowed');

        static::assertTrue($result);
    }

    public function test_onPostDispatchSecure_shoudAssignDataToView_attr_shouldBeFalse()
    {
        Shopware()->Front()->setRequest(new \Enlight_Controller_Request_RequestHttp());

        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->createController();
        $controller->Request()->setParam('sCategory', 36);

        $eventArgs = new \Enlight_Controller_ActionEventArgs();
        $eventArgs->set('subject', $controller);

        $this->getSubscriber()->onPostDispatchSecure($eventArgs);

        $result = $controller->View()->getAssign('paypalIsNotAllowed');

        static::assertFalse($result);
    }

    public function test_onPostDispatchSecure_shoudAssignDataToView_attr_shouldAddProductData()
    {
        Shopware()->Front()->setRequest(new \Enlight_Controller_Request_RequestHttp());

        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->createController();
        $controller->Request()->setParam('sCategory', 6);

        $eventArgs = new \Enlight_Controller_ActionEventArgs();
        $eventArgs->set('subject', $controller);

        $this->getSubscriber()->onPostDispatchSecure($eventArgs);

        $result = Shopware()->Template()->getTemplateVars('riskManagementMatchedProducts');

        static::assertSame('["SW10178"]', $result);

        static::assertFalse($controller->View()->getAssign('paypalIsNotAllowed'));
    }

    public function test_onLoadAjaxListing_shouldNotAssignToView()
    {
        Shopware()->Container()->get('template')->assign('paypalIsNotAllowed', null);
        Shopware()->Container()->get('template')->assign('riskManagementMatchedProducts', null);

        Shopware()->Front()->setRequest(new \Enlight_Controller_Request_RequestHttp());

        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->createController();
        $controller->Request()->setParam('sCategory', 6);
        $controller->Request()->setActionName('someAction');

        $eventArgs = new \Enlight_Controller_ActionEventArgs();
        $eventArgs->set('subject', $controller);

        $this->getSubscriber()->onLoadAjaxListing($eventArgs);

        static::assertNull(Shopware()->Container()->get('template')->getTemplateVars('paypalIsNotAllowed'));
        static::assertNull(Shopware()->Container()->get('template')->getTemplateVars('riskManagementMatchedProducts'));
    }

    public function test_onLoadAjaxListing_shouldAssignToView()
    {
        Shopware()->Front()->setRequest(new \Enlight_Controller_Request_RequestHttp());

        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->createController();
        $controller->Request()->setParam('sCategory', 6);
        $controller->Request()->setActionName('listingCount');

        $eventArgs = new \Enlight_Controller_ActionEventArgs();
        $eventArgs->set('subject', $controller);

        $this->getSubscriber()->onLoadAjaxListing($eventArgs);

        $result = Shopware()->Container()->get('template')->getTemplateVars('riskManagementMatchedProducts');

        static::assertSame('["SW10178"]', $result);
        static::assertFalse($controller->View()->getAssign('paypalIsNotAllowed'));
    }

    /**
     * @return Frontend
     */
    private function getSubscriber()
    {
        return new Frontend(
            Shopware()->Container()->getParameter('paypal_unified.plugin_dir'),
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.risk_management')
        );
    }

    /**
     * @param bool $active
     */
    private function createTestSettings($active = true)
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'clientId' => 'test',
            'clientSecret' => 'test',
            'sandbox' => true,
            'showSidebarLogo' => true,
            'active' => $active,
        ]);
    }

    /**
     * @return DummyController
     */
    private function createController()
    {
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $response = new \Enlight_Controller_Response_ResponseHttp();
        $view = new \Enlight_View_Default(new \Enlight_Template_Manager());

        $controller = new DummyController($request, $view, $response);
        $controller->setContainer(Shopware()->Container());
        $controller->setFront(Shopware()->Front());

        return $controller;
    }
}
