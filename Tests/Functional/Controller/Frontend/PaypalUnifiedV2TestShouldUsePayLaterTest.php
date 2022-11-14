<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Controller_Request_RequestTestCase;
use Generator;
use Shopware_Controllers_Frontend_PaypalUnifiedV2;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacade;
use SwagPaymentPayPalUnified\Components\Services\DispatchValidation;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common\Link;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedV2.php';

class PaypalUnifiedV2TestShouldUsePayLaterTest extends PaypalPaymentControllerTestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;

    /**
     * @dataProvider indexActionCreatePayPalOrderParameterShouldCalledWithPayPalPayLaterTestDataProvider
     *
     * @param PaymentType::* $paymentType
     *
     * @return void
     */
    public function testIndexActionCreatePayPalOrderParameterShouldCalledWithPayPalPayLater($paymentType)
    {
        $sOrderVariables = [
            'sUserData' => require __DIR__ . '/_fixtures/getUser_result.php',
            'sBasket' => require __DIR__ . '/_fixtures/getBasket_result.php',
        ];

        $dependencyProvider = $this->getContainer()->get('paypal_unified.dependency_provider');
        $session = $this->getContainer()->get('session');
        $session->offsetSet('sOrderVariables', $sOrderVariables);

        $dispatchValidator = $this->createMock(DispatchValidation::class);
        $dispatchValidator->method('isInvalid')->willReturn(false);

        $paypalOrderParameterFacade = $this->createMock(PayPalOrderParameterFacade::class);
        $paypalOrderParameterFacade->expects(static::once())->method('createPayPalOrderParameter')
            ->with($paymentType)
            ->willReturn(
                new PayPalOrderParameter(
                    $sOrderVariables['sUserData'],
                    $sOrderVariables['sBasket'],
                    $paymentType,
                    'anyUniqueId',
                    'anyToken',
                    'anyOrderNumber'
                )
            );

        $request = new Enlight_Controller_Request_RequestTestCase();
        if ($paymentType === PaymentType::PAYPAL_PAY_LATER) {
            $request->setParam('paypalUnifiedPayLater', true);
        }

        $link = new Order\Link();
        $link->setRel(Link::RELATION_APPROVE);
        $link->setHref('');

        $order = new Order();
        $order->setLinks([$link]);

        $orderFactory = $this->createMock(OrderFactory::class);
        $orderFactory->method('createOrder')->willReturn($order);

        $orderResource = $this->createMock(OrderResource::class);
        $orderResource->method('create')->willReturn($order);

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            [
                self::SERVICE_DEPENDENCY_PROVIDER => $dependencyProvider,
                self::SERVICE_DISPATCH_VALIDATION => $dispatchValidator,
                self::SERVICE_ORDER_PARAMETER_FACADE => $paypalOrderParameterFacade,
                self::SERVICE_ORDER_FACTORY => $orderFactory,
                self::SERVICE_ORDER_RESOURCE => $orderResource,
            ],
            $request
        );

        $controller->indexAction();

        $session->unsetAll();
    }

    /**
     * @return Generator<array<int,PaymentType::*>>
     */
    public function indexActionCreatePayPalOrderParameterShouldCalledWithPayPalPayLaterTestDataProvider()
    {
        yield 'PaymentType is PaymentType::PAYPAL_CLASSIC_V2' => [
            PaymentType::PAYPAL_CLASSIC_V2,
        ];

        yield 'PaymentType is PaymentType::PAYPAL_PAY_LATER' => [
            PaymentType::PAYPAL_PAY_LATER,
        ];
    }
}
