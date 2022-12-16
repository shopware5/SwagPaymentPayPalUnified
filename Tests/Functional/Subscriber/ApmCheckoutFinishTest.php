<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Event_EventArgs;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Subscriber\ApmCheckoutFinish;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;

class ApmCheckoutFinishTest extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testOnCheckoutFinishActionIsNotFinish()
    {
        $subscriber = $this->getApmCheckoutFinishSubscriber();

        $eventArgs = $this->createEventArgs('anyOtherActionName');

        $subscriber->onCheckoutFinish($eventArgs);

        static::assertEmpty($eventArgs->get('subject')->getView()->getAssign());
    }

    /**
     * @return void
     */
    public function testOnCheckoutFinishActionIsFinishButNoRequestParamIsSet()
    {
        $subscriber = $this->getApmCheckoutFinishSubscriber();

        $eventArgs = $this->createEventArgs('finish');

        $subscriber->onCheckoutFinish($eventArgs);

        static::assertEmpty($eventArgs->get('subject')->getView()->getAssign());
    }

    /**
     * @return void
     */
    public function testOnCheckoutFinishActionShouldAssignAErrorCode()
    {
        $subscriber = $this->getApmCheckoutFinishSubscriber();

        $eventArgs = $this->createEventArgs('finish');
        $eventArgs->get('subject')->getRequest()->setParam('requireContactToMerchant', true);
        $subscriber->onCheckoutFinish($eventArgs);

        $result = $eventArgs->get('subject')->getView()->getAssign();

        static::assertNotEmpty($result);
        static::assertSame(ErrorCodes::APM_PAYMENT_FAILED_CONTACT_MERCHANT, $result['paypalUnifiedErrorCode']);
    }

    /**
     * @return ApmCheckoutFinish
     */
    private function getApmCheckoutFinishSubscriber()
    {
        $subscriber = $this->getContainer()->get('paypal_unified.subscriber.apm_checkout_finish');

        static::assertInstanceOf(ApmCheckoutFinish::class, $subscriber);

        return $subscriber;
    }

    /**
     * @param string $actionName
     *
     * @return Enlight_Event_EventArgs
     */
    private function createEventArgs($actionName)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName($actionName);

        $view = new Enlight_View_Default(new Enlight_Template_Manager());
        $subject = new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase());

        $eventArgs = new Enlight_Event_EventArgs();
        $eventArgs->set('subject', $subject);

        return $eventArgs;
    }
}
