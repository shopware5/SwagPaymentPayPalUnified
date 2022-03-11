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
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Subscriber\Sepa;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class SepaTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use ContainerTrait;
    use SettingsHelperTrait;

    /**
     * @return void
     */
    public function testOnCheckoutWithNoGeneralSettingsShouldReturn()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['name' => PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME]);

        $eventArgs = $this->getEnlightEventArgs(
            new Enlight_Controller_Request_RequestTestCase(),
            $view,
            new Enlight_Controller_Response_ResponseTestCase()
        );

        $subscriber = $this->createSubscriber();

        $subscriber->onCheckout($eventArgs);

        static::assertCount(1, $eventArgs->getSubject()->View()->getAssign());
        static::assertSame(PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME, $eventArgs->getSubject()->View()->getAssign('sPayment')['name']);
    }

    /**
     * @return void
     */
    public function testOnCheckoutWithInactivePaypalShouldReturn()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['name' => PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME]);

        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'active' => false,
        ]);

        $eventArgs = $this->getEnlightEventArgs(
            new Enlight_Controller_Request_RequestTestCase(),
            $view,
            new Enlight_Controller_Response_ResponseTestCase()
        );

        $subscriber = $this->createSubscriber();

        $subscriber->onCheckout($eventArgs);

        static::assertCount(1, $eventArgs->getSubject()->View()->getAssign());
        static::assertSame(PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME, $eventArgs->getSubject()->View()->getAssign('sPayment')['name']);
    }

    /**
     * @return void
     */
    public function testOnCheckoutWitRequestParameterShouldReturn()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['name' => PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME]);

        $this->insertGeneralSettingsFromArray([
            'active' => true,
            'shopId' => 1,
        ]);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('sepaCheckout', true);

        $eventArgs = $this->getEnlightEventArgs(
            $request,
            $view,
            new Enlight_Controller_Response_ResponseTestCase()
        );

        $subscriber = $this->createSubscriber();

        $subscriber->onCheckout($eventArgs);

        static::assertCount(1, $eventArgs->getSubject()->View()->getAssign());
        static::assertSame(PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME, $eventArgs->getSubject()->View()->getAssign('sPayment')['name']);
    }

    /**
     * @return void
     */
    public function testOnCheckoutWithSepaCheckoutShouldReturn()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['name' => PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME]);

        $this->insertGeneralSettingsFromArray([
            'active' => true,
            'shopId' => 1,
        ]);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('sepaCheckout', true);

        $eventArgs = $this->getEnlightEventArgs(
            $request,
            $view,
            new Enlight_Controller_Response_ResponseTestCase()
        );

        $subscriber = $this->createSubscriber();

        $subscriber->onCheckout($eventArgs);

        static::assertCount(1, $eventArgs->getSubject()->View()->getAssign());
        static::assertSame(PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME, $eventArgs->getSubject()->View()->getAssign('sPayment')['name']);
    }

    /**
     * @return void
     */
    public function testOnCheckoutWithoutSPaymentShouldReturn()
    {
        $this->insertGeneralSettingsFromArray([
            'active' => true,
            'shopId' => 1,
        ]);

        $eventArgs = $this->getEnlightEventArgs(
            new Enlight_Controller_Request_RequestTestCase(),
            new ViewMock(new Enlight_Template_Manager()),
            new Enlight_Controller_Response_ResponseTestCase()
        );

        $subscriber = $this->createSubscriber();

        $subscriber->onCheckout($eventArgs);

        static::assertEmpty($eventArgs->getSubject()->View()->getAssign());
    }

    /**
     * @return void
     */
    public function testOnCheckoutShouldAssignSepaData()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['name' => PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME]);

        $settings = [
            'active' => true,
            'shopId' => 1,
            'sandbox' => true,
            'sandboxClientId' => 'testClientId',
            'intent' => 'CAPTURE',
            'buttonStyleShape' => 'rectangle',
            'buttonStyleSize' => 'large',
            'buttonLocale' => 'EN',
        ];

        $this->insertGeneralSettingsFromArray($settings);

        $eventArgs = $this->getEnlightEventArgs(
            new Enlight_Controller_Request_RequestTestCase(),
            $view,
            new Enlight_Controller_Response_ResponseTestCase()
        );

        $subscriber = $this->createSubscriber();

        $subscriber->onCheckout($eventArgs);

        static::assertCount(8, $eventArgs->getSubject()->View()->getAssign());
        static::assertTrue($eventArgs->getSubject()->View()->getAssign('paypalUnifiedSepaPayment'));
        static::assertSame('EUR', $eventArgs->getSubject()->View()->getAssign('paypalUnifiedSpbCurrency'));
        static::assertSame($settings['sandboxClientId'], $eventArgs->getSubject()->View()->getAssign('paypalUnifiedSpbClientId'));
        static::assertSame($settings['intent'], $eventArgs->getSubject()->View()->getAssign('paypalUnifiedSpbIntent'));
        static::assertSame($settings['buttonStyleShape'], $eventArgs->getSubject()->View()->getAssign('paypalUnifiedSpbStyleShape'));
        static::assertSame($settings['buttonStyleSize'], $eventArgs->getSubject()->View()->getAssign('paypalUnifiedSpbStyleSize'));
        static::assertSame($settings['buttonLocale'], $eventArgs->getSubject()->View()->getAssign('paypalUnifiedSpbButtonLocale'));
    }

    /**
     * @return Sepa
     */
    private function createSubscriber()
    {
        return new Sepa(
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('shopware_storefront.context_service')
        );
    }

    /**
     * @return Enlight_Controller_ActionEventArgs
     */
    private function getEnlightEventArgs(
        Enlight_Controller_Request_RequestTestCase $request,
        ViewMock $view,
        Enlight_Controller_Response_ResponseTestCase $response
    ) {
        return new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
            'request' => $request,
            'response' => $response,
        ]);
    }
}
