<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Controllers\Frontend;

require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedApm.php';

use Enlight_Components_Db_Adapter_Pdo_Mysql;
use Shopware\Models\Order\Status;
use Shopware_Controllers_Frontend_PaypalUnifiedApm;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\ConnectionMock;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class PaypalUnifiedApmTest extends PaypalPaymentControllerTestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;

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
        $this->prepareRequestStack();
    }

    /**
     * @dataProvider paymentStateUpdateDataProvider
     *
     * @param PaymentIntentV2::*      $intent
     * @param PaymentStatusV2::*      $paypalOrderState
     * @param Status::PAYMENT_STATE_* $expectedPaymentState
     * @param bool                    $orderWillReturnOrder
     *
     * @return void
     */
    public function testPaymentStateIsUpdatedCorrectly($intent, $paypalOrderState, $expectedPaymentState, $orderWillReturnOrder = true)
    {
        $this->prepareRedirectDataBuilderFactory();
        $this->prepareShopwareOrder(self::SHOPWARE_ORDER_ID);
        $this->givenTheFollowingRequestParametersAreSet([
            'token' => self::PAYPAL_ORDER_ID,
            'basketId' => null,
        ]);

        $capture = new Capture();
        $capture->setId('any');

        $authorization = new Authorization();
        $authorization->setId('any');

        $this->givenThePayPalOrder(
            self::PAYPAL_ORDER_ID,
            (new Order())->assign([
                'id' => self::PAYPAL_ORDER_ID,
                'intent' => $intent,
                'status' => $paypalOrderState,
                'purchaseUnits' => [
                    $this->createConfiguredMock(PurchaseUnit::class, [
                        'getAmount' => $this->createMock(Amount::class),
                        'getPayments' => $this->createConfiguredMock(Payments::class, [
                            'getCaptures' => [$capture],
                            'getAuthorizations' => [$authorization],
                        ]),
                    ]),
                ],
            ]),
            $orderWillReturnOrder
        );
        $this->givenTheCustomer(self::DEFAULT_CUSTOMER_DATA);
        $this->givenTheCart(self::DEFAULT_CART_DATA);

        // If the cart is invalid, no payment logic will be executed.
        $this->givenTheCartIsValid();

        $this->expectPaymentStatusToBeSetTo($expectedPaymentState);

        $settingsServiceMock = $this->getMockedService(self::SERVICE_SETTINGS_SERVICE);

        $settingsServiceMock->method('get')->willReturnMap([
            [SettingsServiceInterface::SETTING_GENERAL_ORDER_NUMBER_PREFIX, SettingsTable::GENERAL, ''],
        ]);

        $connectionMock = (new ConnectionMock())->createConnectionMock(1, ConnectionMock::METHOD_FETCH);

        $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedApm::class,
            [
                self::SERVICE_DBAL_CONNECTION => $connectionMock,
            ]
        )
            ->returnAction();
    }

    /**
     * @return array<string, array{0: PaymentIntentV2::*, 1: PaymentStatusV2::*, 2: Status::PAYMENT_STATE_*}>
     */
    public function paymentStateUpdateDataProvider()
    {
        $template = 'Intent: %s, PayPal Order Status: %s';

        return [
            \sprintf($template, PaymentIntentV2::CAPTURE, PaymentStatusV2::ORDER_COMPLETED) => [
                PaymentIntentV2::CAPTURE,
                PaymentStatusV2::ORDER_COMPLETED,
                Status::PAYMENT_STATE_COMPLETELY_PAID,
            ],
            \sprintf($template, PaymentIntentV2::AUTHORIZE, PaymentStatusV2::ORDER_COMPLETED) => [
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

        $this->request->method('getParam')->willReturnMap($valueMap);
    }

    /**
     * @param string     $orderId
     * @param Order|null $order
     * @param bool       $orderWillReturnOrder
     *
     * @return void
     */
    private function givenThePayPalOrder($orderId, $order = null, $orderWillReturnOrder = true)
    {
        $orderResourceMock = $this->getMockedService(self::SERVICE_ORDER_RESOURCE);

        if ($orderWillReturnOrder) {
            $orderResourceMock->method('capture')->willReturn($order ?: $this->createMock(Order::class));
            $orderResourceMock->method('authorize')->willReturn($order ?: $this->createMock(Order::class));
        }

        $orderResourceMock->method('get')->willReturnMap([
            [$orderId, $order ?: $this->createMock(Order::class)],
        ]);
    }

    /**
     * @param string $orderId
     *
     * @return void
     */
    private function prepareShopwareOrder($orderId)
    {
        $db = $this->createMock(Enlight_Components_Db_Adapter_Pdo_Mysql::class);

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
     * @param int $status
     *
     * @return void
     */
    private function expectPaymentStatusToBeSetTo($status)
    {
        $paymentStatusServiceMock = $this->getMockedService(self::SERVICE_PAYMENT_STATUS_SERVICE);

        $paymentStatusServiceMock->expects(static::once())
            ->method('updatePaymentStatusV2')
            ->with(
                1,
                $status
            );
    }

    /**
     * @return void
     */
    private function givenTheCartIsValid()
    {
        $basketValidatorMock = $this->getMockedService(self::SERVICE_SIMPLE_BASKET_VALIDATOR);

        $basketValidatorMock->method('validate')->willReturn(true);
    }
}
