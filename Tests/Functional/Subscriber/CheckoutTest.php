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
use SwagPaymentPayPalUnified\Subscriber\Checkout;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class CheckoutTest extends TestCase
{
    /**
     * @return void
     */
    public function testOnCheckoutConfirmBothParameter()
    {
        $enlightEventArgs = $this->createEventArgs();

        $enlightEventArgs->getSubject()->Request()->setActionName('confirm');
        $enlightEventArgs->getSubject()->Request()->setParam('payerActionRequired', 1);
        $enlightEventArgs->getSubject()->Request()->setParam('payerInstrumentDeclined', 1);

        $subscriber = $this->createCheckoutSubscriber();

        $subscriber->onCheckoutConfirm($enlightEventArgs);

        $viewResult = $enlightEventArgs->getSubject()->View();

        static::assertTrue((bool) $viewResult->getAssign('payerActionRequired'));
        static::assertTrue((bool) $viewResult->getAssign('payerInstrumentDeclined'));
    }

    /**
     * @return void
     */
    public function testOnCheckoutConfirmOnlyPayerActionRequiredIsset()
    {
        $enlightEventArgs = $this->createEventArgs();

        $enlightEventArgs->getSubject()->Request()->setActionName('confirm');
        $enlightEventArgs->getSubject()->Request()->setParam('payerActionRequired', 1);

        $subscriber = $this->createCheckoutSubscriber();

        $subscriber->onCheckoutConfirm($enlightEventArgs);

        $viewResult = $enlightEventArgs->getSubject()->View();

        static::assertTrue((bool) $viewResult->getAssign('payerActionRequired'));
        static::assertFalse((bool) $viewResult->getAssign('payerInstrumentDeclined'));
    }

    /**
     * @return void
     */
    public function testOnCheckoutConfirmPayerActionRequiredIssetTrue()
    {
        $enlightEventArgs = $this->createEventArgs();

        $enlightEventArgs->getSubject()->Request()->setActionName('confirm');
        $enlightEventArgs->getSubject()->Request()->setParam('payerActionRequired', 1);
        $enlightEventArgs->getSubject()->Request()->setParam('payerInstrumentDeclined', 0);

        $subscriber = $this->createCheckoutSubscriber();

        $subscriber->onCheckoutConfirm($enlightEventArgs);

        $viewResult = $enlightEventArgs->getSubject()->View();

        static::assertTrue((bool) $viewResult->getAssign('payerActionRequired'));
        static::assertFalse((bool) $viewResult->getAssign('payerInstrumentDeclined'));
    }

    /**
     * @return void
     */
    public function testOnCheckoutConfirmOnlyPayerInstrumentDeclinedIsset()
    {
        $enlightEventArgs = $this->createEventArgs();

        $enlightEventArgs->getSubject()->Request()->setActionName('confirm');
        $enlightEventArgs->getSubject()->Request()->setParam('payerInstrumentDeclined', 1);

        $subscriber = $this->createCheckoutSubscriber();

        $subscriber->onCheckoutConfirm($enlightEventArgs);

        $viewResult = $enlightEventArgs->getSubject()->View();

        static::assertFalse((bool) $viewResult->getAssign('payerActionRequired'));
        static::assertTrue((bool) $viewResult->getAssign('payerInstrumentDeclined'));
    }

    /**
     * @return void
     */
    public function testOnCheckoutConfirmPayerInstrumentDeclinedIssetTrue()
    {
        $enlightEventArgs = $this->createEventArgs();

        $enlightEventArgs->getSubject()->Request()->setActionName('confirm');
        $enlightEventArgs->getSubject()->Request()->setParam('payerActionRequired', 0);
        $enlightEventArgs->getSubject()->Request()->setParam('payerInstrumentDeclined', 1);

        $subscriber = $this->createCheckoutSubscriber();

        $subscriber->onCheckoutConfirm($enlightEventArgs);

        $viewResult = $enlightEventArgs->getSubject()->View();

        static::assertFalse((bool) $viewResult->getAssign('payerActionRequired'));
        static::assertTrue((bool) $viewResult->getAssign('payerInstrumentDeclined'));
    }

    /**
     * @return Checkout
     */
    private function createCheckoutSubscriber()
    {
        return new Checkout();
    }

    /**
     * @return Enlight_Controller_ActionEventArgs
     */
    private function createEventArgs()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();

        return new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);
    }
}
