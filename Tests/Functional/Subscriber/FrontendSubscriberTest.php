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

    public function testCanBeCreated()
    {
        $subscriber = $this->getSubscriber();
        static::assertNotNull($subscriber);
    }

    public function testGetSubscribedEventsHasCorrectEvents()
    {
        $events = Frontend::getSubscribedEvents();
        static::assertCount(4, $events);
        static::assertSame('onCollectJavascript', $events['Theme_Compiler_Collect_Plugin_Javascript']);
        static::assertSame('onPostDispatchSecure', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend']);
        static::assertSame('onLoadAjaxListing', $events['Enlight_Controller_Action_PreDispatch_Widgets_Listing']);
        static::assertSame('onCollectTemplateDir', $events['Theme_Inheritance_Template_Directories_Collected']);
    }

    public function testOnCollectJavascript()
    {
        $javascripts = $this->getSubscriber()->onCollectJavascript();

        foreach ($javascripts as $script) {
            static::assertFileExists($script);
        }

        static::assertCount(9, $javascripts);
    }

    public function testOnPostDistpatchSecureWithoutAnySetttings()
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

    public function testOnPostDispatchSecureReturnSettingInactive()
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

    public function testOnPostDispatchSecurePaymentMethodInactive()
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

    public function testOnPostDispatchSecureAssignsVariablesToView()
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

    public function testOnCollectTemplateDir()
    {
        $subscriber = $this->getSubscriber();
        $returnValue = [];

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([]);

        $enlightEventArgs->setReturn($returnValue);

        $subscriber->onCollectTemplateDir($enlightEventArgs);
        $returnValue = $enlightEventArgs->getReturn();

        static::assertDirectoryExists($returnValue[0]);
    }

    public function testOnPostDispatchSecureShouldAssignFalseToView()
    {
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

    public function testOnPostDispatchSecureShoudAssignDataToViewShouldBeTrue()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->createController();
        $controller->Request()->setParam('sArticle', 178);

        $this->setRequestParameterToFront($controller->Request(), 'frontend', 'detail');

        $controller->Request()->setControllerName('detail');
        $controller->Request()->setActionName('index');
        Shopware()->Front()->setRequest($controller->Request());

        $eventArgs = new \Enlight_Controller_ActionEventArgs();
        $eventArgs->set('subject', $controller);

        $this->getSubscriber()->onPostDispatchSecure($eventArgs);

        $result = $controller->View()->getAssign('paypalIsNotAllowed');

        static::assertTrue($result);
    }

    public function testOnPostDispatchSecureShoudAssignDataToViewShouldBeFalse()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->createController();
        $controller->Request()->setParam('sArticle', 112);

        $this->setRequestParameterToFront($controller->Request(), 'frontend', 'detail');

        $eventArgs = new \Enlight_Controller_ActionEventArgs();
        $eventArgs->set('subject', $controller);

        $this->getSubscriber()->onPostDispatchSecure($eventArgs);

        $result = $controller->View()->getAssign('paypalIsNotAllowed');

        static::assertTrue($result);
    }

    public function testOnPostDispatchSecureShoudAssignDataToViewAttrShouldBeFalse()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->createController();
        $controller->Request()->setParam('sCategory', 36);

        $this->setRequestParameterToFront($controller->Request());

        $eventArgs = new \Enlight_Controller_ActionEventArgs();
        $eventArgs->set('subject', $controller);

        $this->getSubscriber()->onPostDispatchSecure($eventArgs);

        $result = $controller->View()->getAssign('paypalIsNotAllowed');

        static::assertFalse($result);
    }

    public function testOnPostDispatchSecureShoudAssignDataToViewAttrShouldAddProductData()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->createController();
        $controller->Request()->setParam('sCategory', 6);

        $this->setRequestParameterToFront($controller->Request());

        $eventArgs = new \Enlight_Controller_ActionEventArgs();
        $eventArgs->set('subject', $controller);

        $this->getSubscriber()->onPostDispatchSecure($eventArgs);

        $result = Shopware()->Template()->getTemplateVars('riskManagementMatchedProducts');

        static::assertSame('["SW10178"]', $result);

        static::assertFalse($controller->View()->getAssign('paypalIsNotAllowed'));
    }

    public function testOnLoadAjaxListingShouldNotAssignToView()
    {
        Shopware()->Container()->get('template')->assign('paypalIsNotAllowed', null);
        Shopware()->Container()->get('template')->assign('riskManagementMatchedProducts', null);

        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->createController();
        $controller->Request()->setParam('sCategory', 6);

        $this->setRequestParameterToFront($controller->Request(), 'widget', 'listing', 'NotListingCount');

        $eventArgs = new \Enlight_Controller_ActionEventArgs();
        $eventArgs->set('subject', $controller);

        $this->getSubscriber()->onLoadAjaxListing($eventArgs);

        static::assertNull(Shopware()->Container()->get('template')->getTemplateVars('paypalIsNotAllowed'));
        static::assertNull(Shopware()->Container()->get('template')->getTemplateVars('riskManagementMatchedProducts'));
    }

    public function testOnLoadAjaxListingShouldAssignToView()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->createController();
        $controller->Request()->setParam('sCategory', 6);

        $this->setRequestParameterToFront($controller->Request(), 'widget', 'listing', 'listingCount');

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

    private function setRequestParameterToFront(
        \Enlight_Controller_Request_RequestHttp $request,
        $module = 'frontend',
        $controller = 'listing',
        $action = 'index'
    ) {
        Shopware()->Container()->get('front')->setRequest($request);
        Shopware()->Container()->get('front')->Request()->setActionName($action);
        Shopware()->Container()->get('front')->Request()->setControllerName($controller);
        Shopware()->Container()->get('front')->Request()->setModuleName($module);
    }
}
