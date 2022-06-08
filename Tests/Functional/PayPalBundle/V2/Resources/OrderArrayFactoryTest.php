<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\PayPalBundle\V2\Resources;

use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use UnexpectedValueException;

class OrderArrayFactoryTest extends TestCase
{
    use ContainerTrait;

    /**
     * @dataProvider toArrayTestDataProvider
     *
     * @param string $paymentType
     * @param bool   $expectException
     *
     * @return void
     */
    public function testToArray($paymentType, Order $order, $expectException = false)
    {
        $toArrayFactory = $this->getContainer()->get('paypal_unified.order_array_factory');

        if ($expectException) {
            $this->expectException(UnexpectedValueException::class);
            $this->expectExceptionMessage(sprintf('OrderToArrayHandler handler for payment type "%s" not found', $paymentType));
        }

        $result = $toArrayFactory->toArray($order, $paymentType);

        if ($expectException) {
            return;
        }

        static::assertTrue(\is_array($result));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function toArrayTestDataProvider()
    {
        yield 'Expect exception' => [
            'anyPaymentType',
            new Order(),
            true,
        ];

        yield 'Expect PAYPAL_CLASSIC_V2' => [
            PaymentType::PAYPAL_CLASSIC_V2,
            new Order(),
        ];

        yield 'Expect PAYPAL_PAY_LATER' => [
            PaymentType::PAYPAL_PAY_LATER,
            new Order(),
        ];

        yield 'Expect PAYPAL_EXPRESS_V2' => [
            PaymentType::PAYPAL_EXPRESS_V2,
            new Order(),
        ];

        yield 'Expect PAYPAL_PAY_UPON_INVOICE_V2' => [
            PaymentType::PAYPAL_PAY_UPON_INVOICE_V2,
            new Order(),
        ];

        yield 'Expect PAYPAL_SMART_PAYMENT_BUTTONS_V2' => [
            PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2,
            new Order(),
        ];

        yield 'Expect APM_BANCONTACT' => [
            PaymentType::APM_BANCONTACT,
            $this->createOrder(PaymentType::APM_BANCONTACT),
        ];

        yield 'Expect APM_BLIK' => [
            PaymentType::APM_BLIK,
            $this->createOrder(PaymentType::APM_BLIK),
        ];

        yield 'Expect APM_EPS' => [
            PaymentType::APM_EPS,
            $this->createOrder(PaymentType::APM_EPS),
        ];

        yield 'Expect APM_GIROPAY' => [
            PaymentType::APM_GIROPAY,
            $this->createOrder(PaymentType::APM_GIROPAY),
        ];

        yield 'Expect APM_IDEAL' => [
            PaymentType::APM_IDEAL,
            $this->createOrder(PaymentType::APM_IDEAL),
        ];

        yield 'Expect APM_MULTIBANCO' => [
            PaymentType::APM_MULTIBANCO,
            $this->createOrder(PaymentType::APM_MULTIBANCO),
        ];

        yield 'Expect APM_MYBANK' => [
            PaymentType::APM_MYBANK,
            $this->createOrder(PaymentType::APM_MYBANK),
        ];

        yield 'Expect APM_SOFORT' => [
            PaymentType::APM_SOFORT,
            $this->createOrder(PaymentType::APM_SOFORT),
        ];

        yield 'Expect APM_TRUSTLY' => [
            PaymentType::APM_TRUSTLY,
            $this->createOrder(PaymentType::APM_TRUSTLY),
        ];
    }

    /**
     * @param string $paymentType
     *
     * @return Order
     */
    private function createOrder($paymentType)
    {
        $amount = new Order\PurchaseUnit\Amount();

        $purchaseUnit = new Order\PurchaseUnit();
        $purchaseUnit->setAmount($amount);

        $applicationContext = new Order\ApplicationContext();

        $paymentSource = new Order\PaymentSource();
        switch ($paymentType) {
            case PaymentType::APM_BANCONTACT:
                $paymentSource->setBancontact(new Order\PaymentSource\Bancontact());
                // no break
            case PaymentType::APM_BLIK:
                $paymentSource->setBlik(new Order\PaymentSource\Blik());
                // no break
            case PaymentType::APM_EPS:
                $paymentSource->setEps(new Order\PaymentSource\Eps());
                // no break
            case PaymentType::APM_GIROPAY:
                $paymentSource->setGiropay(new Order\PaymentSource\Giropay());
                // no break
            case PaymentType::APM_IDEAL:
                $paymentSource->setIdeal(new Order\PaymentSource\Ideal());
                // no break
            case PaymentType::APM_MULTIBANCO:
                $paymentSource->setMultibanco(new Order\PaymentSource\Multibanco());
                // no break
            case PaymentType::APM_MYBANK:
                $paymentSource->setMybank(new Order\PaymentSource\Mybank());
                // no break
            case PaymentType::APM_SOFORT:
                $paymentSource->setSofort(new Order\PaymentSource\Sofort());
                // no break
            case PaymentType::APM_TRUSTLY:
                $paymentSource->setTrustly(new Order\PaymentSource\Trustly());
        }

        $order = new Order();
        $order->setPurchaseUnits([$purchaseUnit]);
        $order->setApplicationContext($applicationContext);
        $order->setPaymentSource($paymentSource);

        return $order;
    }
}
