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
use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Subscriber\Sepa;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class SepaTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use ShopRegistrationTrait;

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
            'buttonLocale' => '',
        ];

        $expectedButtonLocale = 'de_DE';

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
        static::assertSame($expectedButtonLocale, $eventArgs->getSubject()->View()->getAssign('paypalUnifiedSpbButtonLocale'));
    }

    /**
     * @dataProvider addVarsForEligibilityTestDataProvider
     *
     * @param string              $actionName
     * @param array<string,mixed> $settings
     * @param bool                $expectResult
     *
     * @return void
     */
    public function testAddVarsForEligibility($actionName, array $settings, $expectResult)
    {
        $subscriber = $this->createSubscriber();

        if (!empty($settings)) {
            $this->insertGeneralSettingsFromArray($settings);
        }

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName($actionName);

        $eventArgs = $this->getEnlightEventArgs(
            $request,
            $view,
            new Enlight_Controller_Response_ResponseTestCase()
        );

        $subscriber->addVarsForEligibility($eventArgs);

        $results = $view->getAssign();

        if (!$expectResult) {
            static::assertNull($results['paypalUnifiedSpbClientId']);
            static::assertNull($results['paypalUnifiedSpbIntent']);
            static::assertNull($results['paypalUnifiedSpbButtonLocale']);
            static::assertNull($results['paypalUnifiedSpbCurrency']);
            static::assertNull($results['paypalUnifiedSepaPaymentId']);
        } else {
            static::assertNotEmpty($results['paypalUnifiedSpbClientId'], 'paypalUnifiedSpbClientId is empty');
            static::assertNotEmpty($results['paypalUnifiedSpbIntent'], 'paypalUnifiedSpbIntent is empty');
            static::assertNotEmpty($results['paypalUnifiedSpbButtonLocale'], 'paypalUnifiedSpbButtonLocale is empty');
            static::assertNotEmpty($results['paypalUnifiedSpbCurrency'], 'paypalUnifiedSpbCurrency is empty');
            static::assertNotEmpty($results['paypalUnifiedSepaPaymentId'], 'paypalUnifiedSepaPaymentId is empty');
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function addVarsForEligibilityTestDataProvider()
    {
        $defaultSettings = [
            'active' => true,
            'shopId' => 1,
            'sandbox' => true,
            'sandboxClientId' => 'testClientId',
            'intent' => 'CAPTURE',
        ];

        $inactiveSettings = [
            'active' => false,
            'shopId' => 1,
        ];

        yield 'shouldAssignNothing because action name does not match' => [
            'anyAction',
            $defaultSettings,
            false,
        ];

        yield 'shouldAssignNothing because there a no settings' => [
            'payment',
            [],
            false,
        ];

        yield 'shouldAssignNothing because paypal is inactive' => [
            'payment',
            $inactiveSettings,
            false,
        ];

        yield 'shouldAssign because action payment match' => [
            'payment',
            $defaultSettings,
            true,
        ];

        yield 'shouldAssign because action shippingPayment match' => [
            'shippingPayment',
            $defaultSettings,
            true,
        ];
    }

    /**
     * @return Sepa
     */
    private function createSubscriber()
    {
        return new Sepa(
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('paypal_unified.button_locale_service'),
            $this->getContainer()->get('paypal_unified.payment_method_provider')
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
