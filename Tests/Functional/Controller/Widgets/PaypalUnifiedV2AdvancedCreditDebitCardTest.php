<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class PaypalUnifiedV2AdvancedCreditDebitCardTest extends PaypalPaymentControllerTestCase
{
    /**
     * @return void
     */
    public function testCaptureActionLogIfLiabilityShiftIsNotPossible()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('paypalOrderId', '123456789');

        $payPalOrder = $this->createPaypalOrder();

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->expects(static::once())->method('get')->willReturn($payPalOrder);

        $redirectDataBuilderMock = $this->createMock(RedirectDataBuilder::class);
        $redirectDataBuilderMock->expects(static::once())->method('setCode')->willReturnSelf();
        $redirectDataBuilderMock->expects(static::once())->method('setException')->willReturnSelf();

        $redirectDataBuilderFactoryMock = $this->createMock(RedirectDataBuilderFactory::class);
        $redirectDataBuilderFactoryMock->method('createRedirectDataBuilder')->willReturn($redirectDataBuilderMock);

        $controller = $this->getController(
            Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard::class,
            [
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY => $redirectDataBuilderFactoryMock,
            ],
            $request
        );

        $controller->captureAction();
    }

    /**
     * @return Order
     */
    private function createPaypalOrder()
    {
        $authenticationResult = new AuthenticationResult();
        $authenticationResult->setLiabilityShift(AuthenticationResult::LIABILITY_SHIFT_UNKNOWN);

        $card = new Card();
        $card->setAuthenticationResult($authenticationResult);

        $paymentSource = new PaymentSource();
        $paymentSource->setCard($card);

        $payPalOrder = new Order();
        $payPalOrder->setPaymentSource($paymentSource);

        return $payPalOrder;
    }
}
