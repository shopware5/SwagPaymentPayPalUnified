<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Widgets;

use DomainException;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Generator;
use Shopware_Controllers_Widgets_PaypalUnifiedV2PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\_fixtures\SimplePayPalOrderCreator;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

require_once __DIR__ . '/../../../../Controllers/Widgets/PaypalUnifiedV2PayUponInvoice.php';

class PaypalUnifiedV2PayUponInvoicePollActionTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;
    use ContainerTrait;

    /**
     * @dataProvider createOrders
     *
     * @param int $expectedStatusCode
     *
     * @return void
     */
    public function testCaptureAction(Order $order, $expectedStatusCode)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('sUniqueID', $order->getId());

        $response = new Enlight_Controller_Response_ResponseTestCase();

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($order);
        $orderResourceMock->method('capture')->willReturn($order);

        $paypalUnifiedV2PayUponInvoiceWidget = $this->getController(
            Shopware_Controllers_Widgets_PaypalUnifiedV2PayUponInvoice::class,
            [
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
            ],
            $request,
            $response
        );

        $paypalUnifiedV2PayUponInvoiceWidget->pollOrderAction();
        static::assertSame($expectedStatusCode, $response->getHttpResponseCode());
    }

    /**
     * @return void
     */
    public function testErrorIsThrownWithoutPaypalTransactionId()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $orderResourceMock = $this->createMock(OrderResource::class);

        $paypalUnifiedV2PayUponInvoiceWidget = $this->getController(
            Shopware_Controllers_Widgets_PaypalUnifiedV2PayUponInvoice::class,
            [
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
            ],
            $request,
            $response
        );

        $this->expectException(DomainException::class);
        $paypalUnifiedV2PayUponInvoiceWidget->pollOrderAction();
    }

    /**
     * @return Generator
     */
    public function createOrders()
    {
        $order = (new SimplePayPalOrderCreator())->createSimplePayPalOrder();
        $order->setStatus('COMPLETED');

        yield [
            $order,
            200,
        ];

        $order = (new SimplePayPalOrderCreator())->createSimplePayPalOrder();
        $order->setStatus('VOIDED');

        yield [
            $order,
            400,
        ];

        $order = (new SimplePayPalOrderCreator())->createSimplePayPalOrder();
        $order->setStatus('SOME_THING_ELSE');

        yield [
            $order,
            417,
        ];
    }
}
