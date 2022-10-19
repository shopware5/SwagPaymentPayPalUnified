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
use Shopware_Controllers_Frontend_PaypalUnifiedApm;
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

class PaypalUnifiedApmCaptureOrAuthorizeErrorTest extends PaypalPaymentControllerTestCase
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
    public function testReturnActionRequireRestart()
    {
        $controller = $this->createController(['details' => [['issue' => 'DUPLICATE_INVOICE_ID']]]);

        $controller->returnAction();

        static::assertLocationEndsWith($controller->Response(), 'PaypalUnifiedApm/return/token/xxxxxxxxxxxxxxxx');
        static::assertSame(302, $controller->Response()->getHttpResponseCode());
    }

    /**
     * @return void
     */
    public function testReturnActionPayerActionRequired()
    {
        $controller = $this->createController(['details' => [['issue' => 'PAYER_ACTION_REQUIRED']]]);

        $controller->returnAction();

        static::assertLocationEndsWith($controller->Response(), 'checkout/confirm/payerActionRequired/1');
        static::assertSame(302, $controller->Response()->getHttpResponseCode());
    }

    /**
     * @return void
     */
    public function testReturnActionInstrumentDeclined()
    {
        $controller = $this->createController(['details' => [['issue' => 'INSTRUMENT_DECLINED']]]);

        $controller->returnAction();

        static::assertLocationEndsWith($controller->Response(), 'checkout/confirm/payerInstrumentDeclined/1');
        static::assertSame(302, $controller->Response()->getHttpResponseCode());
    }

    /**
     * @return void
     */
    public function testReturnActionUndefinedError()
    {
        $controller = $this->createController(['details' => [['issue' => 'ANY_OTHER_UNDEFINED_ERROR']]]);

        $controller->returnAction();

        static::assertSame(200, $controller->Response()->getHttpResponseCode());
    }

    /**
     * @param array<string,mixed> $captureErrorResponse
     *
     * @return Shopware_Controllers_Frontend_PaypalUnifiedApm
     */
    private function createController(array $captureErrorResponse)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('token', 'xxxxxxxxxxxxxxxx');

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($this->createPaypalOrder());
        $orderResourceMock->method('capture')->willThrowException(
            new RequestException('Error', 0, null, (string) json_encode($captureErrorResponse))
        );

        $simpleBasketValidatorMock = $this->createMock(SimpleBasketValidator::class);
        $simpleBasketValidatorMock->method('validate')->willReturn(true);

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedApm::class,
            [
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $simpleBasketValidatorMock,
            ],
            $request,
            new Enlight_Controller_Response_ResponseTestCase()
        );

        static::assertInstanceOf(Shopware_Controllers_Frontend_PaypalUnifiedApm::class, $controller);

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
