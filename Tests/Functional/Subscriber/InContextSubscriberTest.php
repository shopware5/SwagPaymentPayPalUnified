<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Controller_ActionEventArgs;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Subscriber\InContext;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class InContextSubscriberTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use ShopRegistrationTrait;

    public function testConstruct()
    {
        $subscriber = $this->getSubscriber();

        static::assertNotNull($subscriber);
    }

    public function testGetSubscribedEvents()
    {
        $events = InContext::getSubscribedEvents();

        static::assertTrue(\is_array($events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']));
        static::assertCount(3, $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']);

        static::assertTrue(\is_array($events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'][0]));
        static::assertTrue(\is_array($events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'][1]));
        static::assertTrue(\is_array($events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'][2]));

        static::assertSame('addInContextButton', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'][0][0]);
        static::assertSame('addInfoToPaymentRequest', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'][1][0]);
        static::assertSame('addInContextInfoToRequest', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'][2][0]);
    }

    public function testAddInContextButtonReturnWrongAction()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('foo');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $subscriber = $this->getSubscriber();
        $subscriber->addInContextButton($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedPaymentId'));
    }

    public function testAddInContextButtonReturnUnifiedInactive()
    {
        $paymentMethodProvider = new PaymentMethodProvider(
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('models')
        );

        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addInContextButton($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedPaymentId'));

        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);
    }

    public function testAddInContextButtonReturnPaymentMethodInactive()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $this->importSettings();

        $subscriber = $this->getSubscriber();
        $subscriber->addInContextButton($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedPaymentId'));
    }

    public function testAddInContextButtonReturnNotUseInContext()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $this->importSettings(true);

        $subscriber = $this->getSubscriber();
        $subscriber->addInContextButton($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedPaymentId'));
    }

    public function testAddInContextButtonRightTemplateAssigns()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $this->importSettings(true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addInContextButton($enlightEventArgs);

        static::assertTrue($view->getAssign('paypalUnifiedUseInContext'));
    }

    /**
     * @return void
     */
    public function testAddInContextButtonAssignsVariablesOnConfirm()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();

        $request->setActionName('confirm');
        $request->setParams([
            'inContextCheckout' => true,
            'paypalOrderId' => 'b53e0880-8141-4d72-a02a-aad475809e77',
            'payerId' => '218baa37-9296-4288-a61d-256dea8594f4',
            'basketId' => 'e209dd3b-90b0-4e06-bd67-850fbf23dcac',
        ]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->importSettings(true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addInContextInfoToRequest($enlightEventArgs);

        static::assertTrue($view->getAssign('paypalUnifiedInContextCheckout'));
        static::assertSame('b53e0880-8141-4d72-a02a-aad475809e77', $view->getAssign('paypalUnifiedInContextOrderId'));
        static::assertSame('218baa37-9296-4288-a61d-256dea8594f4', $view->getAssign('paypalUnifiedInContextPayerId'));
        static::assertSame('e209dd3b-90b0-4e06-bd67-850fbf23dcac', $view->getAssign('paypalUnifiedInContextBasketId'));
    }

    /**
     * @return void
     */
    public function testAddInContextInfoToPaymentRequestAssignsVariablesOnPayment()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $request->setActionName('payment');
        $request->setParams([
            'inContextCheckout' => true,
            'paypalOrderId' => 'e6087d09-4109-49aa-93be-13f4ee0baa5d',
            'payerId' => '1880eb91-fb92-4289-9a60-985fba818429',
            'basketId' => 'daf8a0fd-527b-4700-896e-8a19bc71796f',
        ]);

        $response->setRedirect('http://127.0.0.1');

        $controller = new DummyController($request, $view, $response);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => $controller,
            'request' => $request,
            'response' => $response,
        ]);

        $this->importSettings(true, true, true);

        $subscriber = $this->getSubscriber();
        $subscriber->addInfoToPaymentRequest($enlightEventArgs);

        static::assertTrue($controller->Response()->isRedirect());

        if (method_exists(static::class, 'assertStringContainsString')) {
            static::assertStringContainsString(
                '/PaypalUnifiedV2/return/inContextCheckout/1/token/e6087d09-4109-49aa-93be-13f4ee0baa5d/PayerID/1880eb91-fb92-4289-9a60-985fba818429/basketId/daf8a0fd-527b-4700-896e-8a19bc71796f',
                $response->getHeader('Location', '')
            );
        } else {
            static::assertContains(
                '/PaypalUnifiedV2/return/inContextCheckout/1/token/e6087d09-4109-49aa-93be-13f4ee0baa5d/PayerID/1880eb91-fb92-4289-9a60-985fba818429/basketId/daf8a0fd-527b-4700-896e-8a19bc71796f',
                $response->getHeader('Location', '')
            );
        }
    }

    public function testAddInContextInfoToRequestReturnsBecauseWrongAction()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('fake');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();

        static::assertNull($subscriber->addInContextInfoToRequest($enlightEventArgs));
    }

    public function testAddInContextInfoToRequestReturnsBecauseWrongParam()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber = $this->getSubscriber();

        static::assertNull($subscriber->addInContextInfoToRequest($enlightEventArgs));
    }

    public function testAddInContextInfoToRequestReturnsBecauseNoRedirect()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');
        $request->setParam('useInContext', true);

        $response = new Enlight_Controller_Response_ResponseTestCase();

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
            'response' => $response,
        ]);

        $subscriber = $this->getSubscriber();

        static::assertNull($subscriber->addInContextInfoToRequest($enlightEventArgs));
    }

    public function testAddInContextInfoToRequestReturnsBecauseRedirect()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');
        $request->setParam('inContextCheckout', true);

        $response = new Enlight_Controller_Response_ResponseTestCase();
        $response->setHttpResponseCode(302);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
            'request' => $request,
            'response' => $response,
        ]);

        $subscriber = $this->getSubscriber();
        $subscriber->addInfoToPaymentRequest($enlightEventArgs);

        static::assertSame(302, $response->getHttpResponseCode());
        if (method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString(
                '/PaypalUnifiedV2/return/inContextCheckout/1/',
                $response->getHeader('Location', '')
            );

            return;
        }
        static::assertContains('/PaypalUnifiedV2/return/inContextCheckout/1/', $response->getHeader('Location'));
    }

    /**
     * @param bool $active
     * @param bool $useInContext
     * @param bool $sandboxMode
     * @param bool $hasEcSettings
     */
    private function importSettings($active = false, $useInContext = false, $sandboxMode = false, $hasEcSettings = true)
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'active' => $active,
            'sandbox' => $sandboxMode,
            'useInContext' => $useInContext,
            'clientId' => 'test',
            'clientSecret' => 'test',
        ]);

        if ($hasEcSettings) {
            $this->insertExpressCheckoutSettingsFromArray([]);
        }
    }

    /**
     * @return InContext
     */
    private function getSubscriber()
    {
        return new InContext(
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('paypal_unified.payment_method_provider'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('paypal_unified.button_locale_service')
        );
    }
}
