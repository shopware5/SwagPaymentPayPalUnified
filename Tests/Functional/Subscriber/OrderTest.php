<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Subscriber\Order;

class OrderTest extends TestCase
{
    public function testOnFilterOrderAttributesShouldAddNoPaymentType()
    {
        $eventArgs = new \Enlight_Event_EventArgs(['orderParams' => ['paymentID' => 1]]);

        $request = new \Enlight_Controller_Request_RequestHttp();

        Shopware()->Front()->setRequest($request);

        $this->getOrderSubscriber()->onFilterOrderAttributes($eventArgs);

        $result = $eventArgs->getReturn();

        static::assertNull($result);
    }

    public function testOnFilterOrderAttributesShouldAddAPaymentTypeClassic()
    {
        $eventArgs = $this->getEventArgs();

        $request = new \Enlight_Controller_Request_RequestHttp();

        Shopware()->Front()->setRequest($request);

        $this->getOrderSubscriber()->onFilterOrderAttributes($eventArgs);

        $result = $eventArgs->getReturn()['swag_paypal_unified_payment_type'];

        static::assertSame(PaymentType::PAYPAL_CLASSIC, $result);
    }

    public function testOnFilterOrderAttributesShouldAddAPaymentTypePlus()
    {
        $eventArgs = $this->getEventArgs();

        $request = new \Enlight_Controller_Request_RequestHttp();
        $request->setParam('plus', true);

        Shopware()->Front()->setRequest($request);

        $this->getOrderSubscriber()->onFilterOrderAttributes($eventArgs);

        $result = $eventArgs->getReturn()['swag_paypal_unified_payment_type'];

        static::assertSame(PaymentType::PAYPAL_PLUS, $result);
    }

    public function testOnFilterOrderAttributesShouldAddAPaymentTypeExpress()
    {
        $eventArgs = $this->getEventArgs();

        $request = new \Enlight_Controller_Request_RequestHttp();
        $request->setParam('expressCheckout', true);

        Shopware()->Front()->setRequest($request);

        $this->getOrderSubscriber()->onFilterOrderAttributes($eventArgs);

        $result = $eventArgs->getReturn()['swag_paypal_unified_payment_type'];

        static::assertSame(PaymentType::PAYPAL_EXPRESS, $result);
    }

    public function testOnFilterOrderAttributesShouldAddAPaymentTypeSmartPaymentButton()
    {
        $eventArgs = $this->getEventArgs();

        $request = new \Enlight_Controller_Request_RequestHttp();
        $request->setParam('spbCheckout', true);

        Shopware()->Front()->setRequest($request);

        $this->getOrderSubscriber()->onFilterOrderAttributes($eventArgs);

        $result = $eventArgs->getReturn()['swag_paypal_unified_payment_type'];

        static::assertSame(PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS, $result);
    }

    public function testOnFilterOrderAttributesShouldAddAPaymentTypeInvoice()
    {
        $eventArgs = $this->getEventArgs();

        $request = new \Enlight_Controller_Request_RequestHttp();
        $request->setParam('invoiceCheckout', true);
        $request->setParam('plus', true);

        Shopware()->Front()->setRequest($request);

        $this->getOrderSubscriber()->onFilterOrderAttributes($eventArgs);

        $result = $eventArgs->getReturn()['swag_paypal_unified_payment_type'];

        static::assertSame(PaymentType::PAYPAL_INVOICE, $result);
    }

    /**
     * @return Order
     */
    private function getOrderSubscriber()
    {
        return new Order(
            Shopware()->Container()->get('front'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.payment_method_provider')
        );
    }

    /**
     * @return int
     */
    private function getPaymentId()
    {
        $connection = Shopware()->Container()->get('dbal_connection');
        $modelManager = Shopware()->Container()->get('models');

        return (new PaymentMethodProvider($connection, $modelManager))->getPaymentId(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
    }

    /**
     * @return \Enlight_Event_EventArgs
     */
    private function getEventArgs()
    {
        return new \Enlight_Event_EventArgs(['orderParams' => ['paymentID' => $this->getPaymentId()]]);
    }
}
