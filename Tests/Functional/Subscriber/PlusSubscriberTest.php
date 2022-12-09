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
use Enlight_Event_EventArgs;
use Enlight_Template_Manager;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Subscriber\Plus;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\OrderDataServiceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\PaymentInstructionServiceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\PaymentResourceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class PlusSubscriberTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use ShopRegistrationTrait;

    public function testCanBeCreated()
    {
        $subscriber = $this->getSubscriber();
        static::assertNotNull($subscriber);
    }

    public function testGetSubscribedEventsHasCorrectEvents()
    {
        $events = Plus::getSubscribedEvents();
        static::assertSame(
            ['onPostDispatchCheckout', -10],
            $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']
        );
    }

    public function testOnPostDispatchCheckoutShouldReturnPaymentMethodInactive()
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedUsePlus'));

        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);
    }

    public function testOnPostDispatchCheckoutShouldReturnBecauseNoSettingsExists()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedUsePlus'));
    }

    public function testOnPostDispatchCheckoutShouldReturnBecauseIsExpressCheckout()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');
        $request->setParam('expressCheckout', true);

        $this->createTestSettings();

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedUsePlus'));
    }

    public function testOnPostDispatchCheckoutShouldReturnBecauseTheActionIsInvalid()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('invalidSuperAction');
        $view = new ViewMock(new Enlight_Template_Manager());
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $this->createTestSettings();

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedUsePlus'));
    }

    public function testOnPostDispatchCheckoutShouldReturnBecausePlusIsInactive()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $this->createTestSettings(true, false);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedUsePlus'));
    }

    public function testOnPostDispatchCheckoutShouldAssignValueUsePayPalPlus()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        $view->assign('sPayments', [$unifiedPaymentId => ['id' => $unifiedPaymentId]]);
        $view->assign('sBasket', ['content' => []]);

        $this->createTestSettings();

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        static::assertTrue((bool) $view->getAssign('paypalUnifiedUsePlus'));
    }

    public function testOnPostDispatchCheckoutShouldAssignErrorCode()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');
        $request->setParam('paypal_unified_error_code', 5);
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        $view->assign('sPayments', [$unifiedPaymentId => ['id' => $unifiedPaymentId]]);
        $view->assign('sBasket', ['content' => []]);

        $this->createTestSettings();

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        static::assertTrue((bool) $view->getAssign('paypalUnifiedUsePlus'));
        static::assertSame(5, $view->getAssign('paypalUnifiedErrorCode'));
    }

    public function testOnPostDispatchCheckoutOverwritePaymentName()
    {
        $this->createTestSettings(true, true, false, false, true);

        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['id' => $unifiedPaymentId]);
        $view->assign('sBasket', ['content' => []]);
        $view->assign('sUserData', ['additional' => ['payment' => ['id' => $unifiedPaymentId]]]);
        $view->assign('sPayments', [$unifiedPaymentId => ['id' => $unifiedPaymentId]]);
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $request->setActionName('shippingPayment');
        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        $viewAssignments = $view->getAssign();

        static::assertSame('Test Plus Name', $viewAssignments['sPayment']['description']);
        static::assertSame('<br>Test Plus Description', $viewAssignments['sPayment']['additionaldescription']);

        static::assertSame('Test Plus Name', $viewAssignments['sUserData']['additional']['payment']['description']);
        static::assertSame(
            '<br>Test Plus Description',
            $viewAssignments['sUserData']['additional']['payment']['additionaldescription']
        );

        static::assertSame('Test Plus Name', $viewAssignments['sPayments'][$unifiedPaymentId]['description']);
        static::assertSame(
            '<br>Test Plus Description',
            $viewAssignments['sPayments'][$unifiedPaymentId]['additionaldescription']
        );
    }

    public function testOnPostDispatchSecureHandleShippingPaymentDispatchCouldNotCreatePaymentStruct()
    {
        $this->createTestSettings(true, true, true);
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['id' => $unifiedPaymentId]);
        $view->assign('sBasket', ['sCurrencyName' => 'throwException', 'content' => []]);
        $view->assign('sUserData', []);
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $request->setActionName('shippingPayment');
        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedRestylePaymentSelection'));
    }

    public function testOnPostDispatchSecureSetsRestyleCorrectlyIfSettingIsOn()
    {
        $this->createTestSettings(true, true, true);
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['id' => $unifiedPaymentId]);
        $view->assign('sPayments', [$unifiedPaymentId => ['id' => $unifiedPaymentId]]);
        $view->assign('sBasket', ['content' => []]);
        $view->assign('sUserData', []);
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $request->setActionName('shippingPayment');
        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        static::assertTrue((bool) $view->getAssign('paypalUnifiedRestylePaymentSelection'));
    }

    public function testOnPostDispatchSecureSetsRestyleCorrectlyIfSettingIsOff()
    {
        $this->createTestSettings();
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['id' => $unifiedPaymentId]);
        $view->assign('sBasket', ['content' => []]);
        $view->assign('sUserData', []);
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $request->setActionName('shippingPayment');
        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        static::assertFalse((bool) $view->getAssign('paypalUnifiedRestylePaymentSelection'));
    }

    public function testOnPostDispatchSecureHandleShippingPaymentDispatchHandleIntegratingThirdPartyMethods()
    {
        $this->createTestSettings(true, true, false, true);
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['id' => $unifiedPaymentId]);
        $view->assign('sBasket', ['content' => []]);
        $view->assign('sUserData', []);

        $payments = require __DIR__ . '/_fixtures/sPayments.php';
        $payments[$unifiedPaymentId] = [
            'id' => $unifiedPaymentId,
            'name' => PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME,
            'description' => 'PayPal, Lastschrift oder Kreditkarte',
            'additionaldescription' => 'Bezahlung per PayPal - einfach, schnell und sicher. Zahlung per Lastschrift oder Kreditkarte ist auch ohne PayPal Konto möglich',
        ];
        $view->assign('sPayments', $payments);
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $request->setActionName('shippingPayment');
        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);
        $paymentsForPaymentWall = \json_decode($view->getAssign('paypalUnifiedPlusPaymentMethodsPaymentWall'), true)[0];

        static::assertSame('http://4', $paymentsForPaymentWall['redirectUrl']);
        static::assertSame('Rechnung', $paymentsForPaymentWall['methodName']);
        static::assertSame('Sie zahlen einfach und bequem auf Rechnung.', $paymentsForPaymentWall['description']);
    }

    public function testOnPostDispatchSecureHandleFinishDispatch()
    {
        $this->createTestSettings();
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['id' => $unifiedPaymentId]);
        $view->assign('sPayments', [$unifiedPaymentId => ['id' => $unifiedPaymentId]]);
        $view->assign('sBasket', ['content' => []]);
        $view->assign('sUserData', []);
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $request->setActionName('finish');
        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        static::assertSame('testTransactionId', $view->getAssign('sTransactionumber'));
    }

    public function testOnPostDispatchSecureHandleFinishDispatchAddPaymentInstructions()
    {
        $this->createTestSettings();
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['id' => $unifiedPaymentId]);
        $view->assign('sPayments', [$unifiedPaymentId => ['id' => $unifiedPaymentId]]);
        $view->assign('sBasket', ['content' => []]);
        $view->assign('sUserData', []);
        $view->assign('sOrderNumber', 'getPaymentInstructions');
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $request->setActionName('finish');
        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        static::assertSame('testReference', $view->getAssign('sTransactionumber'));
        static::assertSame('testAccountHolder', $view->getAssign('paypalUnifiedPaymentInstructions')['accountHolder']);
    }

    public function testOnPostDispatchSecureHandleConfirmDispatch()
    {
        $this->createTestSettings();
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['id' => $unifiedPaymentId]);
        $view->assign('sPayments', [$unifiedPaymentId => ['id' => $unifiedPaymentId]]);
        $view->assign('sBasket', ['content' => []]);
        $view->assign('sUserData', []);
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $request->setActionName('confirm');
        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getContainer()->get('session')->offsetSet('paypalUnifiedCameFromPaymentSelection', false);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        static::assertSame('PAY-9HW62735H82101921LLK3D4I', $view->getAssign('paypalUnifiedRemotePaymentId'));
        static::assertSame('https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-49W9096312907153R', $view->getAssign('paypalUnifiedApprovalUrl'));
        static::assertSame('de_DE', $view->getAssign('paypalUnifiedLanguageIso'));
    }

    public function testOnPostDispatchSecureHandleConfirmDispatchShouldReturnBecauseOfNoPaymentStruct()
    {
        $this->createTestSettings();
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['id' => $unifiedPaymentId]);
        $view->assign('sPayments', [$unifiedPaymentId => ['id' => $unifiedPaymentId]]);
        $view->assign('sBasket', ['sCurrencyName' => 'throwException', 'content' => []]);
        $view->assign('sUserData', []);
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $request->setActionName('confirm');
        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);
        $viewAssignments = $view->getAssign();

        static::assertNull($viewAssignments['paypalUnifiedRemotePaymentId']);
        static::assertNull($viewAssignments['paypalUnifiedApprovalUrl']);
        static::assertNull($viewAssignments['paypalUnifiedLanguageIso']);
    }

    public function testOnPostDispatchSecureHandleConfirmDispatchReturnCameFromStepTwo()
    {
        $session = Shopware()->Session();
        $this->createTestSettings();
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        $session->offsetSet('paypalUnifiedCameFromPaymentSelection', true);
        $session->offsetSet('paypalUnifiedRemotePaymentId', 'PAY-TestRemotePaymentId');

        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['id' => $unifiedPaymentId]);
        $view->assign('sPayments', [$unifiedPaymentId => ['id' => $unifiedPaymentId]]);
        $view->assign('sBasket', ['content' => []]);
        $view->assign('sUserData', []);
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $request->setActionName('confirm');
        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
        ]);

        $this->getSubscriber()->onPostDispatchCheckout($enlightEventArgs);

        $viewAssignments = $view->getAssign();

        static::assertSame('PAY-TestRemotePaymentId', $viewAssignments['paypalUnifiedRemotePaymentId']);

        $session->offsetUnset('paypalUnifiedCameFromPaymentSelection');
        $session->offsetUnset('paypalUnifiedRemotePaymentId');
    }

    public function testAddPaymentMethodsAttributesPaymentMethodsInactive()
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);

        $eventArgs = new Enlight_Event_EventArgs();
        $eventArgs->setReturn([
            'test' => 'foo',
        ]);

        $result = $this->getSubscriber()->addPaymentMethodsAttributes($eventArgs);

        static::assertEquals(['test' => 'foo'], $result);

        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);
    }

    public function testAddPaymentMethodsAttributesUnifiedInactive()
    {
        $this->createTestSettings(false);
        $eventArgs = new Enlight_Event_EventArgs();
        $eventArgs->setReturn([
            'test' => 'foo',
        ]);

        $result = $this->getSubscriber()->addPaymentMethodsAttributes($eventArgs);

        static::assertEquals(['test' => 'foo'], $result);
    }

    public function testAddPaymentMethodsAttributesPlusInactive()
    {
        $this->createTestSettings(true, false);
        $eventArgs = new Enlight_Event_EventArgs();
        $eventArgs->setReturn([
            'test' => 'foo',
        ]);

        $result = $this->getSubscriber()->addPaymentMethodsAttributes($eventArgs);

        static::assertEquals(['test' => 'foo'], $result);
    }

    public function testAddPaymentMethodsAttributesDoNotIntegrateThirdPartyMethods()
    {
        $this->createTestSettings();
        $eventArgs = new Enlight_Event_EventArgs();
        $eventArgs->setReturn([
            'test' => 'foo',
        ]);

        $result = $this->getSubscriber()->addPaymentMethodsAttributes($eventArgs);

        static::assertEquals(['test' => 'foo'], $result);
    }

    public function testAddPaymentMethodsAttributesAttributeNotSet()
    {
        $this->createTestSettings(true, true, false, true);
        $eventArgs = new Enlight_Event_EventArgs();
        $eventArgs->setReturn([
            [
                'id' => 5,
            ],
            [
                'id' => 6,
            ],
        ]);

        $result = $this->getSubscriber()->addPaymentMethodsAttributes($eventArgs);

        static::assertEquals([['id' => 5], ['id' => 6]], $result);
    }

    public function testAddPaymentMethodsAttributes()
    {
        $this->createTestSettings(true, true, false, true);
        $this->getContainer()->get('dbal_connection')->executeQuery(
            "INSERT INTO `s_core_paymentmeans_attributes` (`paymentmeanID`, `swag_paypal_unified_display_in_plus_iframe`) VALUES ('6', '1');"
        );
        $eventArgs = new Enlight_Event_EventArgs();
        $eventArgs->setReturn([
            [
                'id' => 5,
            ],
            [
                'id' => 6,
            ],
        ]);

        $result = $this->getSubscriber()->addPaymentMethodsAttributes($eventArgs);

        static::assertEquals(
            [
                ['id' => 5],
                [
                    'id' => 6,
                    'swag_paypal_unified_display_in_plus_iframe' => true,
                    'swag_paypal_unified_plus_iframe_payment_logo' => null,
                ],
            ],
            $result
        );
    }

    /**
     * @return array
     */
    private function getGeneralSettingsAsArray()
    {
        return [
            'id' => 1,
            'shopId' => 1,
            'active' => 1,
            'showSidebarLogo' => 0,
            'displayErrors' => 0,
            'brandName' => 'PlusTestBrandName',
        ];
    }

    /**
     * @param bool $active
     * @param bool $plusActive
     * @param bool $restylePaymentSelection
     * @param bool $integrateThirdPartyMethods
     * @param bool $overwritePaymentName
     */
    private function createTestSettings(
        $active = true,
        $plusActive = true,
        $restylePaymentSelection = false,
        $integrateThirdPartyMethods = false,
        $overwritePaymentName = false
    ) {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 2,
            'clientId' => 'test',
            'clientSecret' => 'test',
            'sandbox' => true,
            'showSidebarLogo' => true,
            'active' => $active,
        ]);

        $plusSettings = [
            'shopId' => 1,
            'active' => $plusActive,
            'restyle' => $restylePaymentSelection,
            'integrateThirdPartyMethods' => $integrateThirdPartyMethods,
        ];

        if ($overwritePaymentName) {
            $plusSettings['paymentName'] = 'Test Plus Name';
            $plusSettings['paymentDescription'] = 'Test Plus Description';
        }
        $this->insertGeneralSettingsFromArray($this->getGeneralSettingsAsArray());
        $this->insertPlusSettingsFromArray($plusSettings);
    }

    /**
     * @return Plus
     */
    private function getSubscriber()
    {
        return new Plus(
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('paypal_unified.dependency_provider'),
            $this->getContainer()->get('snippets'),
            $this->getContainer()->get('dbal_connection'),
            new PaymentInstructionServiceMock(),
            new OrderDataServiceMock(),
            $this->getContainer()->get('paypal_unified.plus.payment_builder_service'),
            $this->getContainer()->get('paypal_unified.client_service'),
            new PaymentResourceMock(),
            $this->getContainer()->get('paypal_unified.exception_handler_service'),
            $this->getContainer()->get('paypal_unified.payment_method_provider')
        );
    }

    private function getPaymentMethodProvider()
    {
        $connection = $this->getContainer()->get('dbal_connection');
        $modelManager = $this->getContainer()->get('models');

        return new PaymentMethodProvider(
            $connection,
            $modelManager
        );
    }
}
