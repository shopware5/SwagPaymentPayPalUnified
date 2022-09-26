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
use SwagPaymentPayPalUnified\Components\OrderNumberService;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacade;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\_fixtures\SimplePayPalOrderCreator;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class PaypalUnifiedV2ExpressCheckoutExpressCheckoutFinishActionShouldRedirectToCheckoutConfirmTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;

    /**
     * @return void
     */
    public function testExpressCheckoutFinishAction()
    {
        $sOrderVariables = [
            'sUserData' => require __DIR__ . '/_fixtures/getUser_result.php',
            'sBasket' => require __DIR__ . '/_fixtures/getBasket_result.php',
        ];

        $this->getContainer()->get('session')->offsetSet('sOrderVariables', $sOrderVariables);
        $this->getContainer()->get('session')->offsetSet('sUserId', 1);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('paypalOrderId', 'anyPaypalOrderId');

        $response = new Enlight_Controller_Response_ResponseTestCase();

        $orderNumberServiceMock = $this->createMock(OrderNumberService::class);
        $orderNumberServiceMock->method('getOrderNumber')->willReturn('anyOrderNumber');

        $payPalOrderParameterFacade = $this->createMock(PayPalOrderParameterFacade::class);
        $payPalOrderParameterFacade->expects(static::once())->method('createPayPalOrderParameter')->willReturn(
            $this->createMock(PayPalOrderParameter::class)
        );

        $payPalOrderMock = (new SimplePayPalOrderCreator())->createSimplePayPalOrder();

        $orderFactoryMock = $this->createMock(OrderFactory::class);
        $orderFactoryMock->expects(static::once())->method('createOrder')->willReturn($payPalOrderMock);

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('capture')->willThrowException($this->createPayerActionRequiredRequestException());
        $orderResourceMock->method('get')->willReturn($payPalOrderMock);

        $paypalUnifiedV2ExpressCheckoutController = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout::class,
            [
                self::SERVICE_ORDER_NUMBER_SERVICE => $orderNumberServiceMock,
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_ORDER_PARAMETER_FACADE => $payPalOrderParameterFacade,
                self::SERVICE_ORDER_FACTORY => $orderFactoryMock,
            ],
            $request,
            $response
        );

        $paypalUnifiedV2ExpressCheckoutController->expressCheckoutFinishAction();

        $counter = 0;
        foreach ($response->getHeaders() as $header) {
            if (\strtolower($header['name']) === 'location') {
                static::assertStringEndsWith(
                    'checkout/confirm/payerActionRequired/1',
                    $header['value']
                );

                ++$counter;
            }
        }

        static::assertGreaterThan(0, $counter);
    }

    /**
     * @return RequestException
     */
    private function createPayerActionRequiredRequestException()
    {
        $message = [
            'name' => 'UNPROCESSABLE_ENTITY',
            'details' => [
                [
                    'issue' => 'PAYER_ACTION_REQUIRED',
                    'description' => 'Payer needs to perform the fol (truncated…)',
                    'payload' => [
                        'name' => 'UNPROCESSABLE_ENTITY',
                        'details' => [
                            'issue' => 'PAYER_ACTION_REQUIRED',
                            'description' => 'Payer needs to perform the following action before proceeding with payment.',
                        ],
                        'message' => 'The requested action could not be performed, semantically incorrect, or failed business validation.',
                        'debug_id' => 'anyDebugId',
                        'links' => [
                            [
                                'href' => 'https://developer.paypal.com/docs/api/orders/v2/#error-PAYER_ACTION_REQUIRED',
                                'rel' => 'information_link',
                                'method' => 'GET',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $message = json_encode($message, 1);
        static::assertTrue(\is_string($message));

        return new RequestException(
            'Client error: POST https://api.paypal.com/v2/checkout/orders/7YA003xxxxx05G/capture resulted in a 422 Unprocessable Entity response:',
            99,
            null,
            $message
        );
    }
}
