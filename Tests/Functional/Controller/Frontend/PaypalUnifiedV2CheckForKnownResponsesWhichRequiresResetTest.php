<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Components_Session_Namespace;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Shopware\Components\HttpClient\RequestException;
use Shopware_Controllers_Frontend_PaypalUnifiedV2;
use stdClass;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\Components\Services\Validation\SimpleBasketValidator;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\AssertLocationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\RequestExceptionTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class PaypalUnifiedV2CheckForKnownResponsesWhichRequiresResetTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;
    use RequestExceptionTrait;
    use AssertLocationTrait;

    /**
     * @return void
     */
    public function testReturnActionPayerActionRequired()
    {
        $session = $this->getContainer()->get('session');
        $session->offsetSet('sUserId', 1);
        $session->offsetSet('sOrderVariables', [
            'sBasket' => require __DIR__ . '/_fixtures/getBasket_result.php',
            'sUserData' => require __DIR__ . '/_fixtures/getUser_result.php',
        ]);

        $controller = $this->getPayPalUnifiedV2Controller($this->createPayerActionRequiredRequestException());
        $controller->Request()->setParam('token', 'anyPayPalOrderId');

        $controller->returnAction();

        static::assertLocationEndsWith($controller->Response(), 'checkout/confirm/payerActionRequired/1');
        static::assertSame(302, $controller->Response()->getHttpResponseCode());
    }

    /**
     * @return void
     */
    public function testReturnActionPayerInstrumentDeclined()
    {
        $session = $this->getContainer()->get('session');
        $session->offsetSet('sUserId', 1);
        $session->offsetSet('sOrderVariables', [
            'sBasket' => require __DIR__ . '/_fixtures/getBasket_result.php',
            'sUserData' => require __DIR__ . '/_fixtures/getUser_result.php',
        ]);

        $controller = $this->getPayPalUnifiedV2Controller($this->createInstrumentDeclinedException());
        $controller->Request()->setParam('token', 'anyPayPalOrderId');

        $controller->returnAction();

        static::assertLocationEndsWith($controller->Response(), 'checkout/confirm/payerInstrumentDeclined/1');
        static::assertSame(302, $controller->Response()->getHttpResponseCode());
    }

    /**
     * @return Shopware_Controllers_Frontend_PaypalUnifiedV2
     */
    private function getPayPalUnifiedV2Controller(RequestException $requestException)
    {
        $settingsServiceMock = $this->createMock(SettingsService::class);
        $settingsServiceMock->method('get')->willReturn(true);

        $payPalOrder = new Order();
        $payPalOrder->setIntent(PaymentIntentV2::CAPTURE);

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($payPalOrder);
        $orderResourceMock->method('capture')->willThrowException($requestException);

        $simpleBasketValidatorMock = $this->createMock(SimpleBasketValidator::class);
        $simpleBasketValidatorMock->method('validate')->willReturn(true);

        $sessionMock = $this->createMock(Enlight_Components_Session_Namespace::class);

        $dependencyProviderMock = $this->createMock(DependencyProvider::class);
        $dependencyProviderMock->method('getSession')->willReturn($sessionMock);
        $dependencyProviderMock->method('getModule')->willReturn(new stdClass());

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            [
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $simpleBasketValidatorMock,
                self::SERVICE_DEPENDENCY_PROVIDER => $dependencyProviderMock,
                self::SERVICE_SETTINGS_SERVICE => $settingsServiceMock,
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
            ],
            new Enlight_Controller_Request_RequestTestCase(),
            new Enlight_Controller_Response_ResponseTestCase()
        );

        static::assertInstanceOf(Shopware_Controllers_Frontend_PaypalUnifiedV2::class, $controller);

        return $controller;
    }
}
