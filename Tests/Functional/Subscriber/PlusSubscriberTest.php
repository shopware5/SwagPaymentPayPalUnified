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
use SwagPaymentPayPalUnified\Subscriber\Plus;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class PlusSubscriberTest extends \PHPUnit_Framework_TestCase
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
        $events = Plus::getSubscribedEvents();
        $this->assertEquals('onPostDispatchCheckout', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']);
    }

    public function test_onPostDispatchCheckout_should_return_payment_method_inactive()
    {
        $paymentMethodProvider = new PaymentMethodProvider(Shopware()->Container()->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);

        $subscriber = $this->getSubscriber();

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedUsePlus'));

        $paymentMethodProvider->setPaymentMethodActiveFlag(true);
    }

    public function test_onPostDispatchCheckout_should_return_because_no_settings_exists()
    {
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedUsePlus'));
    }

    public function test_onPostDispatchCheckout_should_return_because_is_express_checkout()
    {
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');
        $request->setParam('expressCheckout', true);

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedUsePlus'));
    }

    public function test_onPostDispatchCheckout_should_return_because_the_action_is_invalid()
    {
        $subscriber = $this->getSubscriber();

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('invalidSuperAction');

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $response = new \Enlight_Controller_Response_ResponseTestCase();

        $this->createTestSettings();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedUsePlus'));
    }

    public function test_onPostDispatchCheckout_should_assign_value_usePayPalPlus()
    {
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');

        $response = new \Enlight_Controller_Response_ResponseTestCase();

        $this->createTestSettings();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertTrue((bool) $view->getAssign('paypalUnifiedUsePlus'));
    }

    public function test_onPostDispatchCheckout_should_assign_error_code()
    {
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');
        $request->setParam('paypal_unified_error_code', 5);

        $response = new \Enlight_Controller_Response_ResponseTestCase();

        $this->createTestSettings();

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertTrue((bool) $view->getAssign('paypalUnifiedUsePlus'));
        $this->assertEquals('5', $view->getAssign('paypalUnifiedErrorCode'));
    }

    public function test_onPostDispatchSecure_assigns_nothing_to_view()
    {
        $subscriber = $this->getSubscriber();
        $this->createTestSettings(false, true, true);

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('shippingPayment');
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertNull($view->getAssign('paypalUnifiedRestylePaymentSelection'));
    }

    public function test_onPostDispatchSecure_sets_restyle_correctly_if_plus_is_inactive()
    {
        $subscriber = $this->getSubscriber();
        $this->createTestSettings(true, false, true);

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('shippingPayment');
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertFalse((bool) $view->getAssign('paypalUnifiedRestylePaymentSelection'));
    }

    public function test_onPostDispatchSecure_sets_restyle_correctly_if_plus_both_is_inactive()
    {
        $subscriber = $this->getSubscriber();
        $this->createTestSettings(true, false);

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('shippingPayment');
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchCheckout($enlightEventArgs);

        $this->assertFalse((bool) $view->getAssign('paypalUnifiedRestylePaymentSelection'));
    }

    public function test_addPaymentMethodsAttributes_payment_methods_inactive()
    {
        $paymentMethodProvider = new PaymentMethodProvider(Shopware()->Container()->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);

        $eventArgs = new \Enlight_Event_EventArgs();
        $eventArgs->setReturn([
            'test' => 'foo',
        ]);

        $subscriber = $this->getSubscriber();

        $result = $subscriber->addPaymentMethodsAttributes($eventArgs);

        $this->assertArraySubset(['test' => 'foo'], $result);

        $paymentMethodProvider->setPaymentMethodActiveFlag(true);
    }

    public function test_addPaymentMethodsAttributes_unified_inactive()
    {
        $this->createTestSettings(false);
        $eventArgs = new \Enlight_Event_EventArgs();
        $eventArgs->setReturn([
            'test' => 'foo',
        ]);

        $subscriber = $this->getSubscriber();

        $result = $subscriber->addPaymentMethodsAttributes($eventArgs);

        $this->assertArraySubset(['test' => 'foo'], $result);
    }

    public function test_addPaymentMethodsAttributes_plus_inactive()
    {
        $this->createTestSettings(true, false);
        $eventArgs = new \Enlight_Event_EventArgs();
        $eventArgs->setReturn([
            'test' => 'foo',
        ]);

        $subscriber = $this->getSubscriber();

        $result = $subscriber->addPaymentMethodsAttributes($eventArgs);

        $this->assertArraySubset(['test' => 'foo'], $result);
    }

    public function test_addPaymentMethodsAttributes_do_not_integrate_third_party_methods()
    {
        $this->createTestSettings();
        $eventArgs = new \Enlight_Event_EventArgs();
        $eventArgs->setReturn([
            'test' => 'foo',
        ]);

        $subscriber = $this->getSubscriber();

        $result = $subscriber->addPaymentMethodsAttributes($eventArgs);

        $this->assertArraySubset(['test' => 'foo'], $result);
    }

    public function test_addPaymentMethodsAttributes_attribute_not_set()
    {
        $this->createTestSettings(true, true, false, true);
        $eventArgs = new \Enlight_Event_EventArgs();
        $eventArgs->setReturn([
            [
                'id' => 5,
            ],
            [
                'id' => 6,
            ],
        ]);

        $subscriber = $this->getSubscriber();

        $result = $subscriber->addPaymentMethodsAttributes($eventArgs);

        $this->assertArraySubset([['id' => 5], ['id' => 6]], $result);
    }

    public function test_addPaymentMethodsAttributes()
    {
        $this->createTestSettings(true, true, false, true);
        Shopware()->Container()->get('dbal_connection')->executeQuery(
            "INSERT INTO `s_core_paymentmeans_attributes` (`paymentmeanID`, `swag_paypal_unified_display_in_plus_iframe`) VALUES ('6', '1');"
        );
        $eventArgs = new \Enlight_Event_EventArgs();
        $eventArgs->setReturn([
            [
                'id' => 5,
            ],
            [
                'id' => 6,
            ],
        ]);

        $subscriber = $this->getSubscriber();

        $result = $subscriber->addPaymentMethodsAttributes($eventArgs);

        $this->assertArraySubset(
            [
                ['id' => 5],
                [
                    'id' => 6,
                    'swag_paypal_unified_display_in_plus_iframe' => 1,
                ],
            ],
            $result
        );
    }

    /**
     * @param bool $active
     * @param bool $plusActive
     * @param bool $restylePaymentSelection
     * @param bool $integrateThirdPartyMethods
     */
    private function createTestSettings(
        $active = true,
        $plusActive = true,
        $restylePaymentSelection = false,
        $integrateThirdPartyMethods = false
    ) {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'clientId' => 'test',
            'clientSecret' => 'test',
            'sandbox' => true,
            'showSidebarLogo' => true,
            'logoImage' => 'TEST',
            'active' => $active,
        ]);

        $this->insertPlusSettingsFromArray([
            'shopId' => 1,
            'active' => $plusActive,
            'restyle' => $restylePaymentSelection,
            'integrateThirdPartyMethods' => $integrateThirdPartyMethods,
        ]);
    }

    /**
     * @return Plus
     */
    private function getSubscriber()
    {
        return new Plus(
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('paypal_unified.dependency_provider'),
            Shopware()->Container()->get('snippets'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.payment_instruction_service'),
            Shopware()->Container()->get('paypal_unified.order_data_service'),
            Shopware()->Container()->get('paypal_unified.plus.payment_builder_service'),
            Shopware()->Container()->get('paypal_unified.client_service'),
            Shopware()->Container()->get('paypal_unified.payment_resource'),
            Shopware()->Container()->get('paypal_unified.exception_handler_service')
        );
    }
}
