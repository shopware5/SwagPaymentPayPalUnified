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
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\Subscriber\CommentSubscriber;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ResetSessionTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;

class CommentSubscriberTest extends TestCase
{
    use ContainerTrait;
    use ResetSessionTrait;
    use ShopRegistrationTrait;

    /**
     * @return void
     */
    public function testOnCheckoutFinishRemoveCustomerCommentFromSessionShouldDoNothing()
    {
        $eventArgs = $this->getEventArgs();

        $eventArgs->get('subject')->Request()->setActionName('AnyMethod');

        $session = $this->getContainer()->get('session');
        $session->offsetSet(AbstractPaypalPaymentController::COMMENT_KEY, 'anyCommentKey');

        $this->getSubscriber()->onCheckoutFinishRemoveCustomerCommentFromSession($eventArgs);

        static::assertSame('anyCommentKey', $session->offsetGet(AbstractPaypalPaymentController::COMMENT_KEY));
    }

    /**
     * @return void
     */
    public function testOnCheckoutFinishRemoveCustomerCommentFromSessionShouldDeleteSessionValue()
    {
        $eventArgs = $this->getEventArgs();

        $eventArgs->get('subject')->Request()->setActionName('finish');

        $session = $this->getContainer()->get('session');
        $session->offsetSet(AbstractPaypalPaymentController::COMMENT_KEY, 'anyCommentKey');

        $this->getSubscriber()->onCheckoutFinishRemoveCustomerCommentFromSession($eventArgs);

        static::assertNull($session->offsetGet(AbstractPaypalPaymentController::COMMENT_KEY));
    }

    /**
     * @return Enlight_Event_EventArgs
     */
    private function getEventArgs()
    {
        $subject = new DummyController(
            new Enlight_Controller_Request_RequestTestCase(),
            new Enlight_View_Default(new Enlight_Template_Manager()),
            new Enlight_Controller_Response_ResponseTestCase()
        );

        $eventArgs = new Enlight_Event_EventArgs();
        $eventArgs->set('subject', $subject);

        return $eventArgs;
    }

    /**
     * @return CommentSubscriber
     */
    private function getSubscriber()
    {
        return new CommentSubscriber(
            $this->getContainer()->get('paypal_unified.dependency_provider')
        );
    }
}
