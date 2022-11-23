<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Event_EventArgs;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\TestCase;
use Shopware_Controllers_Frontend_Checkout;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Subscriber\FraudNet;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class FraudNetTest extends TestCase
{
    use ContainerTrait;
    use ReflectionHelperTrait;
    use ShopRegistrationTrait;

    /**
     * @return void
     */
    public function testOnCheckoutActionNameIsNotEqualToConfirm()
    {
        $fraudNetSubscriber = $this->getFraudNetSubscriber();

        $view = new Enlight_View_Default(new Enlight_Template_Manager());
        $view->assign('sPayment', ['name' => PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME]);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('AnyActionName');

        $subjectMock = $this->createMock(Shopware_Controllers_Frontend_Checkout::class);
        $subjectMock->method('Request')->willReturn($request);
        $subjectMock->method('View')->willReturn($view);

        $enlightEventArgs = new Enlight_Event_EventArgs();
        $enlightEventArgs->set('subject', $subjectMock);

        $fraudNetSubscriber->onCheckout($enlightEventArgs);

        static::assertNull($view->getAssign('fraudNetSessionId'));
        static::assertNull($view->getAssign('fraudNetFlowId'));
        static::assertNull($view->getAssign('fraudnetSandbox'));
        static::assertNull($view->getAssign('usePayPalFraudNet'));
    }

    /**
     * @return void
     */
    public function testOnCheckoutPaymentNameIsNotInList()
    {
        $fraudNetSubscriber = $this->getFraudNetSubscriber();

        $view = new Enlight_View_Default(new Enlight_Template_Manager());
        $view->assign('sPayment', ['name' => 'anyOtherName']);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $subjectMock = $this->createMock(Shopware_Controllers_Frontend_Checkout::class);
        $subjectMock->method('Request')->willReturn($request);
        $subjectMock->method('View')->willReturn($view);

        $enlightEventArgs = new Enlight_Event_EventArgs();
        $enlightEventArgs->set('subject', $subjectMock);

        $fraudNetSubscriber->onCheckout($enlightEventArgs);

        static::assertNull($view->getAssign('fraudNetSessionId'));
        static::assertNull($view->getAssign('fraudNetFlowId'));
        static::assertNull($view->getAssign('fraudnetSandbox'));
        static::assertNull($view->getAssign('usePayPalFraudNet'));
    }

    /**
     * @return void
     */
    public function testOnCheckoutShouldAssignFraudNetSessionIdToViewAndSession()
    {
        $fraudNetSubscriber = $this->getFraudNetSubscriber();

        $view = new Enlight_View_Default(new Enlight_Template_Manager());
        $view->assign('sPayment', ['name' => PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME]);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $subjectMock = $this->createMock(Shopware_Controllers_Frontend_Checkout::class);
        $subjectMock->method('Request')->willReturn($request);
        $subjectMock->method('View')->willReturn($view);

        $enlightEventArgs = new Enlight_Event_EventArgs();
        $enlightEventArgs->set('subject', $subjectMock);

        $fraudNetSubscriber->onCheckout($enlightEventArgs);

        static::assertNotEmpty($view->getAssign('fraudNetSessionId'));
        static::assertNotEmpty($view->getAssign('fraudNetFlowId'));
        static::assertTrue($view->getAssign('usePayPalFraudNet'));

        $result = $this->getContainer()->get('session')->get(FraudNet::FRAUD_NET_SESSION_KEY);
        static::assertSame($view->getAssign('fraudNetSessionId'), $result);
    }

    /**
     * @return void
     */
    public function testCreateFraudNetSessionId()
    {
        $fraudNetSubscriber = $this->getFraudNetSubscriber();

        $reflectionMethod = $this->getReflectionMethod(FraudNet::class, 'createFraudNetSessionId');

        $expectedResult = $reflectionMethod->invoke($fraudNetSubscriber);

        $result = $this->getContainer()->get('session')->get(FraudNet::FRAUD_NET_SESSION_KEY);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return FraudNet
     */
    private function getFraudNetSubscriber()
    {
        $fraudNetSubscriber = $this->getContainer()->get('paypal_unified.subscriber.fraud_net');

        static::assertInstanceOf(FraudNet::class, $fraudNetSubscriber);

        return $fraudNetSubscriber;
    }
}
