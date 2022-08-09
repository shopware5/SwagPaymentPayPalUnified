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
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class SmartPaymentButtonsSubscriberTest extends TestCase
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
        $actualEvents = SmartPaymentButtons::getSubscribedEvents();
        $expectedEvents = [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'addSmartPaymentButtons',
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
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
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
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
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
        ]);
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSmartPaymentButtons($enlightEventArgs);
        static::assertTrue($view->getAssign('paypalUnifiedUseSmartPaymentButtons'));
    }

    public function validActions()
    {
        return [['index'], ['payment']];
    }

    /**
     * @dataProvider validActions
     *
     * @param string $action
     *
     * @return void
     */
    public function testAddSmartPaymentButtonMarks($action)
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'useSmartPaymentButtons' => true,
        ]);
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();

        $request->setActionName($action);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSmartPaymentButtonMarks($enlightEventArgs);
        static::assertTrue($view->getAssign('paypalUnifiedUseSmartPaymentButtonMarks'));
    }

    /**
     * @return void
     */
    public function testAddSmartPaymentButtonMarksWrongAction()
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'useSmartPaymentButtons' => true,
        ]);
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSmartPaymentButtonMarks($enlightEventArgs);
        static::assertNull($view->getAssign('paypalUnifiedUseSmartPaymentButtonMarks'));
    }

    /**
     * @dataProvider validActions
     *
     * @param string $action
     *
     * @return void
     */
    public function testAddSmartPaymentButtonMarksSpbDisabled($action)
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'useSmartPaymentButtons' => false,
        ]);
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();

        $request->setActionName($action);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->getSubscriber()->addSmartPaymentButtonMarks($enlightEventArgs);
        static::assertNull($view->getAssign('paypalUnifiedUseSmartPaymentButtonMarks'));
    }

    /**
     * @return SmartPaymentButtons
     */
    private function getSubscriber()
    {
        return new SmartPaymentButtons(
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('snippets'),
            $this->getContainer()->get('paypal_unified.payment_method_provider'),
            $this->getContainer()->get('paypal_unified.button_locale_service')
        );
    }
}
