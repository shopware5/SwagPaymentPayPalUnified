<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Services\PayUponInvoiceInstructionService;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\AssertLocationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\_fixtures\SimplePayPalOrderCreator;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\ConnectionMock;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

require __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedV2PayUponInvoice.php';

class PaypalUnifiedV2PayUponInvoiceIndexActionTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;
    use AssertLocationTrait;

    /**
     * @after
     *
     * @return void
     */
    public function clearSession()
    {
        $session = $this->getContainer()->get('session');
        $session->offsetUnset('sOrderVariables');
    }

    /**
     * @return void
     */
    public function testIndexAction()
    {
        $orderNumber = '55555555555';

        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $payPalOrder = $this->createPayPalOrder();

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($payPalOrder);
        $orderResourceMock->method('capture')->willReturn($payPalOrder);
        $orderResourceMock->method('create')->willReturn($payPalOrder);

        $sOrderVariablesMock = [
            'sBasket' => require __DIR__ . '/_fixtures/getBasket_result.php',
            'sUserData' => require __DIR__ . '/_fixtures/getUser_result.php',
        ];

        $session = $this->getContainer()->get('session');
        $session->offsetSet('sOrderVariables', $sOrderVariablesMock);

        $dependencyProviderMock = $this->createMock(DependencyProvider::class);
        $dependencyProviderMock->method('getSession')->willReturn($session);

        $paymentInstructionServiceMock = $this->createMock(PayUponInvoiceInstructionService::class);

        $paypalUnifiedV2Controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice::class,
            [
                self::SERVICE_DBAL_CONNECTION => (new ConnectionMock())->createConnectionMock('1', 'fetch'),
                self::SERVICE_ORDER_PARAMETER_FACADE => $this->getContainer()->get('paypal_unified.paypal_order_parameter_facade'),
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_DEPENDENCY_PROVIDER => $dependencyProviderMock,
                self::SERVICE_ORDER_FACTORY => $this->getContainer()->get('paypal_unified.order_factory'),
                self::SERVICE_PAYMENT_INSTRUCTION_SERVICE => $paymentInstructionServiceMock,
            ],
            $request,
            $response
        );

        $paypalUnifiedV2Controller->indexAction();

        static::assertLocationEndsWith($response, '/checkout/finish/sUniqueID/123456789');
        static::assertSame(302, $response->getHttpResponseCode());
    }

    /**
     * @return Order
     */
    private function createPayPalOrder()
    {
        return (new SimplePayPalOrderCreator())->createSimplePayPalOrder();
    }
}
