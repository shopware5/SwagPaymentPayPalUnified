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
use SwagPaymentPayPalUnified\Subscriber\SmartPaymentButtons;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;
use Symfony\Component\HttpFoundation\Response;

class SmartPaymentButtonsSubscriberTest extends TestCase
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
        $actualEvents = SmartPaymentButtons::getSubscribedEvents();
        $expectedEvents = [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [
                ['addSpbInfoOnConfirm'],
                ['addInfoToPaymentRequest'],
                ['addSmartPaymentButtons', 101],
            ],
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Account' => 'addSmartPaymentButtonMarks',
        ];

        static::assertCount(2, $actualEvents);
        static::assertSame($expectedEvents, $actualEvents);
    }

    public function testAddSmartPaymentButtonsWrongAction()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('finish');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSmartPaymentButtons($enlightEventArgs);
        static::assertNull($view->getAssign('paypalUnifiedUseSmartPaymentButtons'));
    }

    public function testAddSmartPaymentButtonsDisabled()
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'useSmartPaymentButtons' => false,
        ]);
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSmartPaymentButtons($enlightEventArgs);
        static::assertNull($view->getAssign('paypalUnifiedUseSmartPaymentButtons'));
    }

    public function testAddSmartPaymentButtonsMerchantLocationGermany()
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'useSmartPaymentButtons' => true,
        ]);
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSmartPaymentButtons($enlightEventArgs);
        static::assertNull($view->getAssign('paypalUnifiedUseSmartPaymentButtons'));
    }

    public function testAddSmartPaymentButtons()
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'useSmartPaymentButtons' => true,
            'merchantLocation' => 'other',
        ]);
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSmartPaymentButtons($enlightEventArgs);
        static::assertTrue($view->getAssign('paypalUnifiedUseSmartPaymentButtons'));
    }

    public function testAddSpbInfoOnConfirmWrongAction()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('checkout');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSpbInfoOnConfirm($enlightEventArgs);
        static::assertNull($view->getAssign('paypalUnifiedSpbCheckout'));
    }

    public function testAddSpbInfoOnConfirmWithoutRequestParameter()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSpbInfoOnConfirm($enlightEventArgs);
        static::assertNull($view->getAssign('paypalUnifiedSpbCheckout'));
    }

    public function testAddSpbInfoOnConfirm()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');
        $request->setParam('spbCheckout', true);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSpbInfoOnConfirm($enlightEventArgs);
        static::assertTrue($view->getAssign('paypalUnifiedSpbCheckout'));
    }

    public function testAddInfoToPaymentRequestWrongAction()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('checkout');
        $request->setParam('spbCheckout', true);
        $response = new \Enlight_Controller_Response_ResponseTestCase();
        $response->setHttpResponseCode(Response::HTTP_FOUND);
        $enlightEventArgs = $this->getEnlightEventArgs($request, $view, $response);

        $this->getSubscriber()->addInfoToPaymentRequest($enlightEventArgs);

        static::assertNull($response->getHeader('Location'));
    }

    public function testAddInfoToPaymentRequestWithoutRequestParameter()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');
        $response = new \Enlight_Controller_Response_ResponseTestCase();
        $response->setHttpResponseCode(Response::HTTP_FOUND);
        $enlightEventArgs = $this->getEnlightEventArgs($request, $view, $response);

        $this->getSubscriber()->addInfoToPaymentRequest($enlightEventArgs);

        static::assertNull($response->getHeader('Location'));
    }

    public function testAddInfoToPaymentRequestNotRedirectedToAction()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');
        $request->setParam('spbCheckout', true);
        $response = new \Enlight_Controller_Response_ResponseTestCase();
        $response->setHttpResponseCode(Response::HTTP_OK);
        $enlightEventArgs = $this->getEnlightEventArgs($request, $view, $response);

        $this->getSubscriber()->addInfoToPaymentRequest($enlightEventArgs);

        static::assertNull($response->getHeader('Location'));
    }

    public function testAddInfoToPaymentRequest()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');
        $request->setParam('spbCheckout', true);
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $response->setHttpResponseCode(Response::HTTP_FOUND);
        $enlightEventArgs = $this->getEnlightEventArgs($request, $view, $response);

        $this->getSubscriber()->addInfoToPaymentRequest($enlightEventArgs);

        static::assertSame(302, $response->getHttpResponseCode());
        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString(
                '/PaypalUnified/return/spbCheckout/1/paymentId//PayerID//basketId/',
                $response->getHeader('Location')
            );

            return;
        }
        static::assertContains(
            '/PaypalUnified/return/spbCheckout/1/paymentId//PayerID//basketId/',
            $response->getHeader('Location')
        );
    }

    public function testaddSmartPaymentButtonMarks()
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'useSmartPaymentButtons' => true,
            'merchantLocation' => 'other',
        ]);
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();

        $validActions = ['index', 'payment'];
        $request->setActionName($validActions[\random_int(0, 1)]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSmartPaymentButtonMarks($enlightEventArgs);
        static::assertTrue($view->getAssign('paypalUnifiedUseSmartPaymentButtonMarks'));
    }

    public function testaddSmartPaymentButtonMarksWrongAction()
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'useSmartPaymentButtons' => true,
            'merchantLocation' => 'other',
        ]);
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSmartPaymentButtonMarks($enlightEventArgs);
        static::assertNull($view->getAssign('paypalUnifiedUseSmartPaymentButtonMarks'));
    }

    public function testaddSmartPaymentButtonMarksSpbDisabled()
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'useSmartPaymentButtons' => false,
            'merchantLocation' => 'other',
        ]);
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();

        $validActions = ['index', 'payment'];
        $request->setActionName($validActions[\random_int(0, 1)]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSmartPaymentButtonMarks($enlightEventArgs);
        static::assertNull($view->getAssign('paypalUnifiedUseSmartPaymentButtonMarks'));
    }

    public function testaddSmartPaymentButtonMarksGermanMerchantLocation()
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'useSmartPaymentButtons' => true,
            'merchantLocation' => 'germany',
        ]);
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();

        $validActions = ['index', 'payment'];
        $request->setActionName($validActions[\random_int(0, 1)]);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSmartPaymentButtonMarks($enlightEventArgs);
        static::assertNull($view->getAssign('paypalUnifiedUseSmartPaymentButtonMarks'));
    }

    /**
     * @param Enlight_Controller_Request_RequestTestCase   $request
     * @param ViewMock                                     $view
     * @param Enlight_Controller_Response_ResponseTestCase $response
     *
     * @return Enlight_Controller_ActionEventArgs
     */
    private function getEnlightEventArgs($request, $view, $response)
    {
        return new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, $response),
            'request' => $request,
            'response' => $response,
        ]);
    }

    /**
     * @return SmartPaymentButtons
     */
    private function getSubscriber()
    {
        return new SmartPaymentButtons(
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('snippets')
        );
    }
}
