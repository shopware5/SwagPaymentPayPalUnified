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
use Enlight_View_Default;
use Generator;
use PHPUnit\Framework\TestCase;
use Shopware_Controllers_Frontend_Checkout;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Subscriber\PayUponInvoice;

class PayUponInvoiceTest extends TestCase
{
    /**
     * @dataProvider onCheckoutTestDataProvider
     *
     * @param bool $expectedResult
     *
     * @return void
     */
    public function testOnCheckout(Enlight_Controller_ActionEventArgs $args, $expectedResult)
    {
        $subscriber = new PayUponInvoice();

        $subscriber->onCheckout($args);

        $result = $args->getSubject()->View()->getAssign('showPayUponInvoiceLegalText');

        static::assertSame($result, $expectedResult);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function onCheckoutTestDataProvider()
    {
        yield 'ActionName and PaymentMethodName are empty' => [
            $this->createEnlightEventArgs(),
            null,
        ];

        yield 'ActionName and PaymentMethodName does not match' => [
            $this->createEnlightEventArgs('anyAction', 'anyPaymentMethod'),
            null,
        ];

        yield 'PaymentMethodName are empty' => [
            $this->createEnlightEventArgs('confirm'),
            null,
        ];

        yield 'PaymentMethodName does not match' => [
            $this->createEnlightEventArgs('confirm', 'anyPaymentMethod'),
            null,
        ];

        yield 'Should assign showPayUponInvoiceLegalText => true' => [
            $this->createEnlightEventArgs('confirm', PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME),
            true,
        ];
    }

    /**
     * @param string|null $actionName
     * @param string|null $paymentMethodName
     *
     * @return Enlight_Controller_ActionEventArgs|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createEnlightEventArgs($actionName = null, $paymentMethodName = null)
    {
        $sPayment = ['name' => $paymentMethodName];

        $view = new Enlight_View_Default(new Enlight_Template_Manager());
        $view->assign('sPayment', $sPayment);

        $request = new Enlight_Controller_Request_RequestTestCase();

        if ($actionName !== null) {
            $request->setActionName($actionName);
        }

        $controller = $this->createMock(Shopware_Controllers_Frontend_Checkout::class);
        $controller->method('View')->willReturn($view);

        $eventArgs = $this->createMock(Enlight_Controller_ActionEventArgs::class);
        $eventArgs->method('getRequest')->willReturn($request);
        $eventArgs->method('getSubject')->willReturn($controller);

        return $eventArgs;
    }
}
