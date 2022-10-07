<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Controllers\Frontend;

use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use SwagPaymentPayPalUnified\Components\OrderNumberService;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentControllerResults\PatchOrderNumberResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPaymentControllerPatchOrderNumberTest extends PaypalPaymentControllerTestCase
{
    use ReflectionHelperTrait;

    const TRANSACTION_ID = '3E630337S9748511R';
    const ORDER_NUMBER = '2001';
    const METHOD_NAME = 'patchOrderNumber';

    /**
     * @return void
     */
    public function testHandleOrderWithSendOrderNumber()
    {
        $orderNumberServiceMock = $this->getOrderNumberServiceMock();

        $abstractController = $this->getController(AbstractPaypalPaymentController::class, [
            self::SERVICE_ORDER_NUMBER_SERVICE => $orderNumberServiceMock,
        ]);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, self::METHOD_NAME);

        /** @var PatchOrderNumberResult $result */
        $result = $reflectionMethod->invoke($abstractController, $this->createPayPalOrder());

        static::assertTrue($result->getSuccess());
        static::assertSame(self::ORDER_NUMBER, $result->getShopwareOrderNumber());
    }

    /**
     * @return void
     */
    public function testHandleOrderWithSendOrderNumberAndUpdatePayPalOrderFails()
    {
        $orderNumberServiceMock = $this->getOrderNumberServiceMock();
        $orderResource = $this->createMock(OrderResource::class);
        $orderResource->expects(static::once())->method('update')->willThrowException(new RuntimeException('Any Error'));

        $abstractController = $this->getController(AbstractPaypalPaymentController::class, [
            self::SERVICE_ORDER_NUMBER_SERVICE => $orderNumberServiceMock,
            self::SERVICE_ORDER_RESOURCE => $orderResource,
        ]);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, self::METHOD_NAME);

        /** @var PatchOrderNumberResult $result */
        $result = $reflectionMethod->invoke($abstractController, $this->createPayPalOrder());

        static::assertFalse($result->getSuccess());
        static::assertSame(self::ORDER_NUMBER, $result->getShopwareOrderNumber());
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
        $payee->setEmailAddress('test@business.example.com');

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

    /**
     * @return OrderNumberService&MockObject
     */
    private function getOrderNumberServiceMock()
    {
        $orderNumberServiceMock = $this->createMock(OrderNumberService::class);
        $orderNumberServiceMock->expects(static::once())->method('getOrderNumber')->willReturn(self::ORDER_NUMBER);

        return $orderNumberServiceMock;
    }
}
