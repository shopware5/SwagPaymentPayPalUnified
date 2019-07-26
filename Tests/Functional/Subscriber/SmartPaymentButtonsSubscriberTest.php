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
use Enlight_Template_Manager;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Subscriber\SmartPaymentButtons;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

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
        $events = SmartPaymentButtons::getSubscribedEvents();

        static::assertCount(1, $events);
        static::assertSame('addSmartPaymentButtons', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']);
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

    /**
     * @return SmartPaymentButtons
     */
    private function getSubscriber()
    {
        return new SmartPaymentButtons(
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('dbal_connection')
        );
    }
}
