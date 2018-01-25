<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Template_Manager;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Subscriber\Frontend;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class FrontendSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

    public function test_can_be_created()
    {
        $subscriber = $this->getSubscriber();
        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents_has_correct_events()
    {
        $events = Frontend::getSubscribedEvents();
        $this->assertCount(3, $events);
        $this->assertEquals('onCollectJavascript', $events['Theme_Compiler_Collect_Plugin_Javascript']);
        $this->assertEquals('onPostDispatchSecure', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend']);
        $this->assertEquals('onCollectTemplateDir', $events['Theme_Inheritance_Template_Directories_Collected']);
    }

    public function test_onCollectJavascript()
    {
        $subscriber = $this->getSubscriber();
        $javascripts = $subscriber->onCollectJavascript();

        foreach ($javascripts as $script) {
            $this->assertFileExists($script);
        }

        $this->assertCount(9, $javascripts);
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

        $this->assertNull($result);
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

        $this->assertNull($view->getAssign('paypalUnifiedShowLogo'));
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

        $this->assertFalse($view->getAssign('paypalUnifiedShowLogo'));

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

        $this->assertTrue((bool) $view->getAssign('paypalUnifiedShowLogo'));
        $this->assertTrue((bool) $view->getAssign('paypalUnifiedShowInstallmentsLogo'));
        $this->assertTrue((bool) $view->getAssign('paypalUnifiedAdvertiseReturns'));
    }

    public function test_onCollectTemplateDir()
    {
        $subscriber = $this->getSubscriber();
        $returnValue = [];

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
        ]);

        $enlightEventArgs->setReturn($returnValue);

        $subscriber->onCollectTemplateDir($enlightEventArgs);
        $returnValue = $enlightEventArgs->getReturn();

        $this->assertDirectoryExists($returnValue[0]);
    }

    /**
     * @return Frontend
     */
    private function getSubscriber()
    {
        return new Frontend(
            Shopware()->Container()->getParameter('paypal_unified.plugin_dir'),
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('dbal_connection')
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
            'logoImage' => 'TEST',
            'active' => $active,
            'advertiseReturns' => true,
        ]);

        $this->insertInstallmentsSettingsFromArray([
            'active' => true,
            'showLogo' => true,
        ]);
    }
}
