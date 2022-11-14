<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Widgets;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\NumberRangeIncrementerDecorator;
use SwagPaymentPayPalUnified\Components\OrderNumberService;
use SwagPaymentPayPalUnified\Components\Services\Validation\SimpleBasketValidator;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\_fixtures\SimplePayPalOrderCreator;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\ConnectionMock;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class PaypalUnifiedV2AdvancedCreditDebitCardCaptureActionTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;
    use ContainerTrait;

    /**
     * @return void
     */
    public function testCaptureAction()
    {
        $orderNumber = '44444444444';

        $session = $this->getContainer()->get('session');
        $session->offsetSet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY, $orderNumber);
        $session->offsetSet('sUserId', 1);
        $session->offsetSet('sOrderVariables', [
            'sBasket' => require __DIR__ . '/../Frontend/_fixtures/getBasket_result.php',
            'sUserData' => require __DIR__ . '/../Frontend/_fixtures/getUser_result.php',
        ]);

        $dependencyProviderMock = $this->createMock(DependencyProvider::class);
        $dependencyProviderMock->method('getSession')->willReturn($session);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('paypalOrderId', '123456789');

        $response = new Enlight_Controller_Response_ResponseTestCase();

        $payPalOrder = $this->createPayPalOrder();

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($payPalOrder);
        $orderResourceMock->method('capture')->willReturn($payPalOrder);

        $simpleBasketValidatorMock = $this->createMock(SimpleBasketValidator::class);
        $simpleBasketValidatorMock->method('validate')->willReturn(true);

        $orderNumberServiceMock = $this->createMock(OrderNumberService::class);
        $orderNumberServiceMock->method('getOrderNumber')->willReturn($orderNumber);

        $paypalUnifiedV2AdvancedCreditDebitCardController = $this->getController(
            Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard::class,
            [
                self::SERVICE_DBAL_CONNECTION => (new ConnectionMock())->createConnectionMock('1', 'fetch'),
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $simpleBasketValidatorMock,
                self::SERVICE_DEPENDENCY_PROVIDER => $dependencyProviderMock,
                self::SERVICE_ORDER_NUMBER_SERVICE => $orderNumberServiceMock,
            ],
            $request,
            $response
        );

        $paypalUnifiedV2AdvancedCreditDebitCardController->captureAction();

        $paypalOrderIdResult = $session->offsetGet('paypalOrderId');

        static::assertSame('123456789', $paypalOrderIdResult);
    }

    /**
     * @return Order
     */
    private function createPayPalOrder()
    {
        $order = (new SimplePayPalOrderCreator())->createSimplePayPalOrder();

        $authenticationResult = new AuthenticationResult();
        $authenticationResult->setLiabilityShift(AuthenticationResult::LIABILITY_SHIFT_POSSIBLE);

        $card = new Card();
        $card->setAuthenticationResult($authenticationResult);

        $paymentSource = new PaymentSource();
        $paymentSource->setCard($card);

        $order->setPaymentSource($paymentSource);

        return $order;
    }
}
