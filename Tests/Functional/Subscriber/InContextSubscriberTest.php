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

        static::assertSame('addInContextButton', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']);
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
