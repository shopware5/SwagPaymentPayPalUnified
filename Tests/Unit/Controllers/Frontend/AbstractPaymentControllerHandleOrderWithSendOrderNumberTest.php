<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Controllers\Frontend;

require_once __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedApm.php';

use Exception;
use Generator;
use ReflectionClass;
use Shopware_Controllers_Frontend_PaypalUnifiedApm as PaypalUnifiedApm;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Services\CartRestoreService;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentControllerResults\HandleOrderWithSendOrderNumberResult;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPaymentControllerHandleOrderWithSendOrderNumberTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;
    use SettingsHelperTrait;

    const SESSION_ID = 'phpUnitTestSessionId';

    const TRANSACTION_ID = '3E630337S9748511R';

    /**
     * @before
     *
     * @return void
     */
    public function createBasket()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/basket.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);
    }

    /**
     * @after
     *
     * @return void
     */
    public function cleanUpDatabase()
    {
        $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->delete('s_order_basket')
            ->where('sessionID = :sessionId')
            ->setParameter('sessionId', self::SESSION_ID)
            ->execute();

        $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->delete('s_order')
            ->where('transactionID = :transactionId')
            ->setParameter('transactionId', self::TRANSACTION_ID)
            ->execute();

        $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->delete('swag_payment_paypal_unified_settings_general')
            ->where('shop_id = 1')
            ->execute();
    }

    /**
     * @dataProvider handleOrderWithSendOrderNumberTestDataProvider
     *
     * @param bool $orderResourceWillThrowException
     *
     * @return void
     */
    public function testHandleOrderWithSendOrderNumber(Order $paypalOrder, $orderResourceWillThrowException = false)
    {
        $this->insertGeneralSettingsFromArray(['active' => 1]);
        $basketRestoreService = $this->createBasketRestoreService();
        $orderDataService = $this->getContainer()->get('paypal_unified.order_data_service');
        $paymentStatusService = $this->getContainer()->get('paypal_unified.payment_status_service');
        $orderResourceMock = $this->createMock(OrderResource::class);

        if ($orderResourceWillThrowException) {
            $orderResourceMock->method('update')->willThrowException(new Exception('PhpUnitException'));
        }

        $this->prepareRequestStack();
        $controller = $this->getController(
            PaypalUnifiedApm::class,
            null,
            $this->getContainer()->get('paypal_unified.redirect_data_builder_factory'),
            null,
            null,
            null,
            $orderResourceMock,
            null,
            null,
            $orderDataService,
            null,
            null,
            null,
            $paymentStatusService,
            null,
            $basketRestoreService
        );

        $reflectionMethod = (new ReflectionClass(PaypalUnifiedApm::class))->getMethod('handleOrderWithSendOrderNumber');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($controller, [$paypalOrder, PaymentType::APM_GIROPAY]);

        $basketResult = $basketRestoreService->getCartData();

        static::assertInstanceOf(HandleOrderWithSendOrderNumberResult::class, $result);

        if ($orderResourceWillThrowException) {
            static::assertFalse($result->getSuccess());
            static::assertCount(8, $basketResult);
        } else {
            static::assertTrue($result->getSuccess());
            static::assertEmpty($basketResult);
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function handleOrderWithSendOrderNumberTestDataProvider()
    {
        yield 's_order_basket should be empty because no exception occurred' => [
            $this->createPayPalOrder(),
            false,
        ];

        yield 's_order_basket should not be empty' => [
            $this->createPayPalOrder(),
            true,
        ];
    }

    /**
     * @return CartRestoreService
     */
    private function createBasketRestoreService()
    {
        $userData = require __DIR__ . '/../../../_fixtures/s_user_data.php';

        $session = $this->getContainer()->get('session');
        $session->set('id', self::SESSION_ID);
        $session->offsetSet('sessionId', self::SESSION_ID);
        $session->set('sUserId', 1);
        $session->set('sOrderVariables', $userData);

        $dependencyProviderMock = $this->createMock(DependencyProvider::class);
        $dependencyProviderMock->method('getSession')->willReturn($session);

        $cartRestoreService = $this->getContainer()->get('paypal_unified.cart_restore_service');

        $reflectionProperty = (new ReflectionClass(CartRestoreService::class))->getProperty('dependencyProvider');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($cartRestoreService, $dependencyProviderMock);

        return $cartRestoreService;
    }

    /**
     * @return Order
     */
    private function createPayPalOrder()
    {
        $amount = new Order\PurchaseUnit\Amount();
        $amount->setValue('347.89');
        $amount->setCurrencyCode('EUR');

        $payee = new Order\PurchaseUnit\Payee();
        $payee->setEmailAddress('sb-h3rzg14140643@business.example.com');

        $purchaseUnit = new Order\PurchaseUnit();
        $purchaseUnit->setAmount($amount);
        $purchaseUnit->setPayee($payee);

        $giroPay = new Order\PaymentSource\Giropay();
        $giroPay->setCountryCode('DE');
        $giroPay->setName('Max Mustermann');

        $paymentSource = new Order\PaymentSource();
        $paymentSource->setGiropay($giroPay);

        $order = new Order();
        $order->setId(self::TRANSACTION_ID);
        $order->setIntent('CAPTURE');
        $order->setCreateTime('2022-04-25T06:51:36Z');
        $order->setStatus('APPROVED');
        $order->setPurchaseUnits([$purchaseUnit]);
        $order->setPaymentSource($paymentSource);

        return $order;
    }
}
