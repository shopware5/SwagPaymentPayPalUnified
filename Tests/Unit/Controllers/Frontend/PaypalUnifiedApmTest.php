<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Controllers\Frontend;

require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedApm.php';

use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PaypalUnifiedApmTest extends PaypalPaymentControllerTestCase
{
    use ContainerTrait;

    const PAYPAL_ORDER_ID = '85713e7f-b5c1-4f4b-b28b-8a9626bb3209';
    const SHOPWARE_ORDER_ID = '749d7b69-b542-498e-93a2-2c06ffc07d9b';
    const CUSTOMER_ID = '52ad326b-6fc7-42cb-8f3c-6bf585a7ea94';

    const DEFAULT_CUSTOMER_DATA = [
        'additional' => [],
    ];

    const DEFAULT_CART_DATA = [
        'content' => [],
    ];

    /**
     * @before
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->prepareRequestStack();
    }

    /**
     * @dataProvider paymentStateUpdateDataProvider
     *
     * @param PaymentIntentV2::*      $intent
     * @param PaymentStatusV2::*      $paypalOrderState
     * @param Status::PAYMENT_STATE_* $expectedPaymentState
     *
     * @return void
     */
    public function testPaymentStateIsUpdatedCorrectly($intent, $paypalOrderState, $expectedPaymentState)
    {
        $this->prepareRedirectDataBuilderFactory();
        $this->prepareShopwareOrder(self::SHOPWARE_ORDER_ID);
        $this->givenTheFollowingRequestParametersAreSet([
            'token' => self::PAYPAL_ORDER_ID,
            'basketId' => null,
        ]);
        $this->givenThePayPalOrder(self::PAYPAL_ORDER_ID, (new Order())->assign([
            'id' => self::PAYPAL_ORDER_ID,
            'intent' => $intent,
            'status' => $paypalOrderState,
            'purchaseUnits' => [
                static::createConfiguredMock(Order\PurchaseUnit::class, [
                    'getAmount' => static::createMock(Order\PurchaseUnit\Amount::class),
                    'getPayments' => static::createConfiguredMock(Order\PurchaseUnit\Payments::class, [
                        'getCaptures' => [static::createMock(Order\PurchaseUnit\Payments\Capture::class)],
                        'getAuthorizations' => [static::createMock(Order\PurchaseUnit\Payments\Authorization::class)],
                    ]),
                ]),
            ],
        ]));
        $this->givenTheCustomer(self::DEFAULT_CUSTOMER_DATA);
        $this->givenTheCart(self::DEFAULT_CART_DATA);

        // If the cart is invalid, no payment logic will be executed.
        $this->givenTheCartIsValid();

        $this->expectPaymentStatusToBeSetTo($expectedPaymentState);

        $this->settingsService->method('get')->willReturn(static::returnValueMap([
            [SettingsServiceInterface::SETTING_GENERAL_SEND_ORDER_NUMBER, SettingsTable::GENERAL, true],
        ]));
        $this->getController(\Shopware_Controllers_Frontend_PaypalUnifiedApm::class)
            ->returnAction();
    }

    /**
     * @return array<string, array{0: PaymentIntentV2::*, 1: PaymentStatusV2::*, 2: Status::PAYMENT_STATE_*}>
     */
    public function paymentStateUpdateDataProvider()
    {
        $template = 'Intent: %s, PayPal Order Status: %s';

        return [
            sprintf($template, PaymentIntentV2::CAPTURE, PaymentStatusV2::ORDER_AUTHORIZATION_DENIED) => [
                PaymentIntentV2::CAPTURE,
                PaymentStatusV2::ORDER_AUTHORIZATION_DENIED,
                Status::PAYMENT_STATE_REVIEW_NECESSARY,
            ],
            sprintf($template, PaymentIntentV2::CAPTURE, PaymentStatusV2::ORDER_COMPLETED) => [
                PaymentIntentV2::CAPTURE,
                PaymentStatusV2::ORDER_COMPLETED,
                Status::PAYMENT_STATE_COMPLETELY_PAID,
            ],
            sprintf($template, PaymentIntentV2::AUTHORIZE, PaymentStatusV2::ORDER_AUTHORIZATION_DENIED) => [
                PaymentIntentV2::AUTHORIZE,
                PaymentStatusV2::ORDER_AUTHORIZATION_DENIED,
                Status::PAYMENT_STATE_REVIEW_NECESSARY,
            ],
            sprintf($template, PaymentIntentV2::AUTHORIZE, PaymentStatusV2::ORDER_COMPLETED) => [
                PaymentIntentV2::AUTHORIZE,
                PaymentStatusV2::ORDER_COMPLETED,
                Status::PAYMENT_STATE_RESERVED,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return void
     */
    private function givenTheFollowingRequestParametersAreSet($parameters)
    {
        $valueMap = array_map(static function ($key, $value) {
            return [$key, null, $value];
        }, array_keys($parameters), array_values($parameters));

        $this->request->method('getParam')
            ->will(static::returnValueMap($valueMap));
    }

    /**
     * @param string     $orderId
     * @param Order|null $order
     *
     * @return void
     */
    private function givenThePayPalOrder($orderId, $order = null)
    {
        $this->orderResource->method('get')
            ->will(static::returnValueMap([
                [$orderId, $order ?: static::createMock(Order::class)],
            ]));
    }

    /**
     * @param string $orderId
     *
     * @return void
     */
    private function prepareShopwareOrder($orderId)
    {
        $db = static::createMock(\Enlight_Components_Db_Adapter_Pdo_Mysql::class);

        $db->method('fetchOne')
            ->willReturn($orderId);

        $db->method('insert')
            ->willReturn(1);

        $db->method('lastInsertId')
            ->willReturn(1);

        $this->getContainer()->set('db', $db);
    }

    /**
     * @param array<string, mixed> $cart
     *
     * @return void
     */
    private function givenTheCart($cart)
    {
        $session = $this->getContainer()->get('session');
        $orderVariables = $session->offsetGet('sOrderVariables') ?: [];

        $orderVariables['sBasket'] = $cart;

        $this->getContainer()->get('session')
            ->offsetSet('sOrderVariables', $orderVariables);
    }

    /**
     * @param array<string, mixed> $customer
     *
     * @return void
     */
    private function givenTheCustomer($customer)
    {
        $session = $this->getContainer()->get('session');
        $orderVariables = $session->offsetGet('sOrderVariables') ?: [];

        $orderVariables['sUserData'] = $customer;

        $this->getContainer()->get('session')
            ->offsetSet('sOrderVariables', $orderVariables);
    }

    /**
     * @throws \Enlight_Exception
     *
     * @return void
     */
    private function prepareRequestStack()
    {
        $requestStack = $this->getContainer()->get('request_stack', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        if ($requestStack instanceof RequestStack) {
            $requestStack->push($this->request);
        }
        $this->getContainer()->get('front')->setRequest($this->request);
    }

    /**
     * @param int         $status
     * @param string|null $paypalOrderId
     *
     * @return void
     */
    private function expectPaymentStatusToBeSetTo($status, $paypalOrderId = null)
    {
        $this->paymentStatusService->expects(static::once())
            ->method('updatePaymentStatus')
            ->with(
                $paypalOrderId ?: self::PAYPAL_ORDER_ID,
                $status
            );
    }

    /**
     * @return void
     */
    private function givenTheCartIsValid()
    {
        $this->basketValidator->method('validate')
            ->willReturn(true);
    }
}
