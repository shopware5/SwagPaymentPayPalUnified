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
use Shopware\Components\HttpClient\RequestException;
use Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacade;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\Validation\SimpleBasketValidator;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\AssertLocationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ResetSessionTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class PaypalUnifiedV2ExpressCheckoutCaptureOrAuthorizeOrderErrorTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;
    use ResetSessionTrait;
    use AssertLocationTrait;

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
    public function resetSessionAfter()
    {
        $this->resetSession();
    }

    /**
     * @return void
     */
    public function testExpressCheckoutFinishRequireRestart()
    {
        $controller = $this->createController(['details' => [['issue' => 'DUPLICATE_INVOICE_ID']]]);

        $controller->expressCheckoutFinishAction();

        static::assertLocationEndsWith($controller->Response(), 'PaypalUnifiedV2ExpressCheckout/expressCheckoutFinish/token/xxxxxxxxxxxxxxxx');
        static::assertSame(302, $controller->Response()->getHttpResponseCode());
    }

    /**
     * @return void
     */
    public function testExpressCheckoutFinishPayerActionRequired()
    {
        $controller = $this->createController(['details' => [['issue' => 'PAYER_ACTION_REQUIRED']]]);

        $controller->expressCheckoutFinishAction();

        static::assertLocationEndsWith($controller->Response(), 'checkout/confirm/payerActionRequired/1');
        static::assertSame(302, $controller->Response()->getHttpResponseCode());
    }

    /**
     * @return void
     */
    public function testExpressCheckoutFinishInstrumentDeclined()
    {
        $controller = $this->createController(['details' => [['issue' => 'INSTRUMENT_DECLINED']]]);

        $controller->expressCheckoutFinishAction();

        static::assertLocationEndsWith($controller->Response(), 'checkout/confirm/payerInstrumentDeclined/1');
        static::assertSame(302, $controller->Response()->getHttpResponseCode());
    }

    /**
     * @return void
     */
    public function testReturnActionUndefinedError()
    {
        $controller = $this->createController(['details' => [['issue' => 'ANY_OTHER_UNDEFINED_ERROR']]]);

        $controller->expressCheckoutFinishAction();

        static::assertSame(200, $controller->Response()->getHttpResponseCode());
    }

    /**
     * @param array<string,mixed> $captureErrorResponse
     *
     * @return Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout
     */
    private function createController(array $captureErrorResponse)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('token', 'xxxxxxxxxxxxxxxx');

        $payPalOrderParameterMock = $this->createMock(PayPalOrderParameter::class);

        $payPalOrderParameterFacadeMock = $this->createMock(PayPalOrderParameterFacade::class);
        $payPalOrderParameterFacadeMock->method('createPayPalOrderParameter')->willReturn($payPalOrderParameterMock);

        $orderFactoryMock = $this->createMock(OrderFactory::class);
        $orderFactoryMock->method('createOrder')->willReturn($this->createPaypalOrder());

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($this->createPaypalOrder());
        $orderResourceMock->method('capture')->willThrowException(
            new RequestException('Error', 0, null, (string) json_encode($captureErrorResponse))
        );

        $simpleBasketValidatorMock = $this->createMock(SimpleBasketValidator::class);
        $simpleBasketValidatorMock->method('validate')->willReturn(true);

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout::class,
            [
                self::SERVICE_ORDER_PARAMETER_FACADE => $payPalOrderParameterFacadeMock,
                self::SERVICE_ORDER_FACTORY => $orderFactoryMock,
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $simpleBasketValidatorMock,
            ],
            $request,
            new Enlight_Controller_Response_ResponseTestCase()
        );

        static::assertInstanceOf(Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout::class, $controller);

        return $controller;
    }

    /**
     * @return Order
     */
    private function createPaypalOrder()
    {
        $authenticationResult = new AuthenticationResult();
        $authenticationResult->setLiabilityShift(AuthenticationResult::LIABILITY_SHIFT_POSSIBLE);

        $card = new Card();
        $card->setAuthenticationResult($authenticationResult);

        $paymentSource = new PaymentSource();
        $paymentSource->setCard($card);

        $payPalOrder = new Order();
        $payPalOrder->setPaymentSource($paymentSource);
        $payPalOrder->setIntent(PaymentIntentV2::CAPTURE);

        return $payPalOrder;
    }
}
