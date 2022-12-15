<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseHttp;
use SwagPaymentPayPalUnified\Components\Services\OrderPropertyHelper;
use SwagPaymentPayPalUnified\Components\Services\Validation\SimpleBasketValidator;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Giropay;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\AssertLocationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\_mocks\PaypalUnifiedApmMock;
use SwagPaymentPayPalUnified\Tests\Functional\ResetSessionTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class PaypalUnifiedApmPaymentCompletedFailedTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;
    use ResetSessionTrait;
    use AssertLocationTrait;
    use ContainerTrait;

    /**
     * @before
     *
     * @return void
     */
    public function prepareSession()
    {
        $session = $this->getContainer()->get('session');
        $session->offsetSet('sOrderVariables', [
            'sBasket' => require __DIR__ . '/../Frontend/_fixtures/getBasket_result.php',
            'sUserData' => require __DIR__ . '/../Frontend/_fixtures/getUser_result.php',
        ]);
    }

    /**
     * @after
     *
     * @return void
     */
    public function resetSessionAndContainerAfter()
    {
        $this->resetSession();
    }

    /**
     * @return void
     */
    public function testReturnActionShouldRedirectToFinishWithRequireContactToMerchantParameter()
    {
        $controller = $this->createController();

        $controller->returnAction();

        static::assertLocationEndsWith($controller->Response(), 'requireContactToMerchant/1');
    }

    /**
     * @return PaypalUnifiedApmMock
     */
    private function createController()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('token', 'xxxxxxxxxxxxxxxx');

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($this->createPaypalOrder());

        $simpleBasketValidatorMock = $this->createMock(SimpleBasketValidator::class);
        $simpleBasketValidatorMock->method('validate')->willReturn(true);

        $orderPropertyHelperMock = $this->createMock(OrderPropertyHelper::class);
        $orderPropertyHelperMock->method('getFirstCapture')->willReturn($this->createCapture());

        $controller = $this->getController(
            PaypalUnifiedApmMock::class,
            [
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $simpleBasketValidatorMock,
                self::SERVICE_DBAL_CONNECTION => $this->getContainer()->get('dbal_connection'),
                self::SERVICE_ORDER_PROPERTY_HELPER => $orderPropertyHelperMock,
            ],
            $request,
            new Enlight_Controller_Response_ResponseHttp()
        );

        static::assertInstanceOf(PaypalUnifiedApmMock::class, $controller);

        return $controller;
    }

    /**
     * @return Order
     */
    private function createPaypalOrder()
    {
        $authenticationResult = new AuthenticationResult();
        $authenticationResult->setLiabilityShift(AuthenticationResult::LIABILITY_SHIFT_POSSIBLE);

        $capture = $this->createCapture();

        $payments = new Payments();
        $payments->setCaptures([$capture]);

        $amount = new Amount();

        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setAmount($amount);
        $purchaseUnit->setPayments($payments);

        $giropay = new Giropay();

        $paymentSource = new PaymentSource();
        $paymentSource->setGiropay($giropay);

        $payPalOrder = new Order();
        $payPalOrder->setPaymentSource($paymentSource);
        $payPalOrder->setIntent(PaymentIntentV2::CAPTURE);

        $payPalOrder->setPurchaseUnits([$purchaseUnit]);

        return $payPalOrder;
    }

    /**
     * @return Capture
     */
    private function createCapture()
    {
        $capture = new Capture();
        $capture->setStatus(PaymentStatusV2::ORDER_CAPTURE_PENDING);

        return $capture;
    }
}
