<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Services;

use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\OrderPropertyHelper;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice\DepositBankDetails;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;

class OrderPropertyHelperTest extends TestCase
{
    /**
     * @dataProvider getPaymentsTestDataProvider
     *
     * @param bool $expectResult
     *
     * @return void
     */
    public function testGetPayments(Order $order, $expectResult = false)
    {
        $service = new OrderPropertyHelper();

        $result = $service->getPayments($order);

        if ($expectResult) {
            static::assertInstanceOf(Payments::class, $result);
        } else {
            static::assertNull($result);
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getPaymentsTestDataProvider()
    {
        yield 'With only a default order' => [
            $this->getSimpleOrder(),
        ];

        yield 'With only a default order and empty array as purchaseUnit' => [
            $this->getSimpleOrderWithEmptyArrayAsPurchaseUnits(),
        ];

        yield 'With only a default order and one purchaseUnit without payment' => [
            $this->getSimpleOrderWithPurchaseUnit(),
        ];

        yield 'Should return a Payment object' => [
            $this->getSimpleOrderWithPayments(),
            true,
        ];
    }

    /**
     * @dataProvider getAuthorizationTestDataProvider
     *
     * @param bool $expectResult
     *
     * @return void
     */
    public function testGetAuthorization(Order $order, $expectResult = false)
    {
        $service = new OrderPropertyHelper();

        $result = $service->getAuthorization($order);

        if ($expectResult) {
            static::assertInstanceOf(Authorization::class, $result);
        } else {
            static::assertNull($result);
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getAuthorizationTestDataProvider()
    {
        yield 'Order without Authorization' => [
            $this->getSimpleOrderWithPayments(),
        ];

        yield 'Order with empty array as Authorization' => [
            $this->getSimpleOrderWithEmptyAuthorization(),
        ];

        yield 'Order with Authorization' => [
            $this->getSimpleOrderWithAuthorization(),
            true,
        ];
    }

    /**
     * @dataProvider getFirstCaptureTestDataProvider
     *
     * @param bool $expectResult
     *
     * @return void
     */
    public function testGetFirstCapture(Order $order, $expectResult = false)
    {
        $service = new OrderPropertyHelper();

        $result = $service->getFirstCapture($order);

        if ($expectResult) {
            static::assertInstanceOf(Capture::class, $result);
        } else {
            static::assertNull($result);
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getFirstCaptureTestDataProvider()
    {
        yield 'Order with empty captures' => [
            $this->getSimpleOrderWithPayments(),
        ];

        yield 'Order with empty capture array' => [
            $this->getOrderWithEmptyCaptureArray(),
        ];

        yield 'Order with capture' => [
            $this->getOrderWithCapture(),
            true,
        ];
    }

    /**
     * @dataProvider getBankDetailsTestDataProvider
     *
     * @return void
     */
    public function testGetBankDetails(Order $paypalOrder, DepositBankDetails $expectedResult = null)
    {
        $service = new OrderPropertyHelper();

        $result = $service->getBankDetails($paypalOrder);

        if ($expectedResult === null) {
            static::assertNull($result);
        } else {
            static::assertInstanceOf(DepositBankDetails::class, $result);
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getBankDetailsTestDataProvider()
    {
        yield 'Order without PaymentSource' => [
            new Order(),
        ];

        yield 'Order without PayUponInvoice' => [
            $this->createOrderWithPaymentSource(),
        ];

        yield 'Order without DepositBankDetails' => [
            $this->createOrderWithPayUponInvoice(),
        ];

        yield 'Order with DepositBankDetails' => [
            $this->createOrderWithDepositBankDetails(),
            $this->createDepositBankDetails(),
        ];
    }

    /**
     * @return Order
     */
    private function createOrderWithDepositBankDetails()
    {
        $order = new Order();
        $paymentSource = new PaymentSource();
        $payUponInvoice = new PayUponInvoice();
        $payUponInvoice->setDepositBankDetails($this->createDepositBankDetails());
        $paymentSource->setPayUponInvoice($payUponInvoice);
        $order->setPaymentSource($paymentSource);

        return $order;
    }

    /**
     * @return DepositBankDetails
     */
    private function createDepositBankDetails()
    {
        $depositBankDetails = new DepositBankDetails();
        $depositBankDetails->setAccountHolderName('Max Mustermann');
        $depositBankDetails->setBankName('Muster Bank');
        $depositBankDetails->setBic('any BIC');
        $depositBankDetails->setIban('any IBAN');

        return $depositBankDetails;
    }

    /**
     * @return Order
     */
    private function createOrderWithPayUponInvoice()
    {
        $order = new Order();
        $paymentSource = new PaymentSource();
        $paymentSource->setPayUponInvoice(new PayUponInvoice());
        $order->setPaymentSource($paymentSource);

        return $order;
    }

    /**
     * @return Order
     */
    private function createOrderWithPaymentSource()
    {
        $order = new Order();
        $order->setPaymentSource(new PaymentSource());

        return $order;
    }

    /**
     * @return Order
     */
    private function getOrderWithCapture()
    {
        $order = $this->getSimpleOrder();

        $purchaseUnit = new PurchaseUnit();
        $order->setPurchaseUnits([$purchaseUnit]);
        $payments = $this->getSimplePayments();

        $capture = new Capture();

        $purchaseUnit->setPayments($payments);
        $payments->setCaptures([$capture]);

        return $order;
    }

    /**
     * @return Order
     */
    private function getOrderWithEmptyCaptureArray()
    {
        $order = $this->getSimpleOrder();

        $purchaseUnit = new PurchaseUnit();
        $order->setPurchaseUnits([$purchaseUnit]);
        $payments = $this->getSimplePayments();

        $purchaseUnit->setPayments($payments);
        $payments->setCaptures([]);

        return $order;
    }

    /**
     * @return Payments
     */
    private function getSimplePayments()
    {
        $payments = new Payments();

        return $payments;
    }

    /**
     * @return Order
     */
    private function getSimpleOrderWithAuthorization()
    {
        $order = $this->getSimpleOrder();

        $purchaseUnit = new PurchaseUnit();
        $order->setPurchaseUnits([$purchaseUnit]);
        $payments = $this->getSimplePayments();

        $authorization = new Authorization();

        $purchaseUnit->setPayments($payments);
        $payments->setAuthorizations([$authorization]);

        return $order;
    }

    /**
     * @return Order
     */
    private function getSimpleOrderWithEmptyAuthorization()
    {
        $order = $this->getSimpleOrder();

        $purchaseUnit = new PurchaseUnit();
        $order->setPurchaseUnits([$purchaseUnit]);
        $payments = $this->getSimplePayments();

        $purchaseUnit->setPayments($payments);
        $payments->setAuthorizations([]);

        return $order;
    }

    /**
     * @return Order
     */
    private function getSimpleOrderWithPayments()
    {
        $order = $this->getSimpleOrder();

        $purchaseUnit = new PurchaseUnit();
        $order->setPurchaseUnits([$purchaseUnit]);
        $payments = $this->getSimplePayments();
        $purchaseUnit->setPayments($payments);

        return $order;
    }

    /**
     * @return Order
     */
    private function getSimpleOrderWithPurchaseUnit()
    {
        $order = $this->getSimpleOrder();

        $purchaseUnit = new PurchaseUnit();
        $order->setPurchaseUnits([$purchaseUnit]);

        return $order;
    }

    /**
     * @return Order
     */
    private function getSimpleOrderWithEmptyArrayAsPurchaseUnits()
    {
        $order = $this->getSimpleOrder();
        $order->setPurchaseUnits([]);

        return $order;
    }

    /**
     * @return Order
     */
    private function getSimpleOrder()
    {
        $order = new Order();
        $order->setId('123456');

        return $order;
    }
}
