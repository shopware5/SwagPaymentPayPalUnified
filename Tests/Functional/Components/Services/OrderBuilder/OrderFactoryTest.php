<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\OrderBuilder;

use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use UnexpectedValueException;

class OrderFactoryTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use SettingsHelperTrait;
    use DatabaseTestCaseTrait;

    /**
     * @dataProvider createOrderTestDataProvider
     *
     * @param array<string,mixed>|bool $expectException
     *
     * @return void
     */
    public function testCreateOrder(PayPalOrderParameter $orderParameter, $expectException = false)
    {
        $orderFactory = $this->getContainer()->get('paypal_unified.order_factory');

        $this->insertGeneralSettingsFromArray(['active' => 1]);

        if ($expectException) {
            $this->expectException(UnexpectedValueException::class);
            $this->expectExceptionMessage(sprintf('Create order handler for payment type "%s" not found', $orderParameter->getPaymentType()));
        }

        $order = $orderFactory->createOrder($orderParameter);

        if ($expectException) {
            return;
        }

        static::assertInstanceOf(Order::class, $order);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function createOrderTestDataProvider()
    {
        yield 'Expect exception because PaymentType does not match' => [
            new PayPalOrderParameter([], [], 'anyPaymentType', null, null),
            true,
        ];

        yield 'Expect order with PaymentType::PAYPAL_CLASSIC_V2' => [
            $this->createPaypalOrderParameter(PaymentType::PAYPAL_CLASSIC_V2),
        ];

        yield 'Expect order with PaymentType::PAYPAL_PAY_LATER' => [
            $this->createPaypalOrderParameter(PaymentType::PAYPAL_PAY_LATER),
        ];

        yield 'Expect order with PaymentType::PAYPAL_PAY_UPON_INVOICE_V2' => [
            $this->createPaypalOrderParameter(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2),
        ];

        yield 'Expect order with PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD' => [
            $this->createPaypalOrderParameter(PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD),
        ];

        yield 'Expect order with PaymentType::PAYPAL_EXPRESS_V2' => [
            $this->createPaypalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2),
        ];

        yield 'Expect order with PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2' => [
            $this->createPaypalOrderParameter(PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2),
        ];

        yield 'Expect order with PaymentType::APM_BANCONTACT' => [
            $this->createPaypalOrderParameter(PaymentType::APM_BANCONTACT),
        ];

        yield 'Expect order with PaymentType::APM_BLIK' => [
            $this->createPaypalOrderParameter(PaymentType::APM_BLIK),
        ];

        yield 'Expect order with PaymentType::APM_EPS' => [
            $this->createPaypalOrderParameter(PaymentType::APM_EPS),
        ];

        yield 'Expect order with PaymentType::APM_GIROPAY' => [
            $this->createPaypalOrderParameter(PaymentType::APM_GIROPAY),
        ];

        yield 'Expect order with PaymentType::APM_IDEAL' => [
            $this->createPaypalOrderParameter(PaymentType::APM_IDEAL),
        ];

        yield 'Expect order with PaymentType::APM_MULTIBANCO' => [
            $this->createPaypalOrderParameter(PaymentType::APM_MULTIBANCO),
        ];

        yield 'Expect order with PaymentType::APM_MYBANK' => [
            $this->createPaypalOrderParameter(PaymentType::APM_MYBANK),
        ];

        yield 'Expect order with PaymentType::APM_OXXO' => [
            $this->createPaypalOrderParameter(PaymentType::APM_OXXO),
        ];

        yield 'Expect order with PaymentType::APM_P24' => [
            $this->createPaypalOrderParameter(PaymentType::APM_P24),
        ];

        yield 'Expect order with PaymentType::APM_SOFORT' => [
            $this->createPaypalOrderParameter(PaymentType::APM_SOFORT),
        ];

        yield 'Expect order with PaymentType::APM_TRUSTLY' => [
            $this->createPaypalOrderParameter(PaymentType::APM_TRUSTLY),
        ];
    }

    /**
     * @param PaymentType::* $paymentType
     *
     * @return PayPalOrderParameter
     */
    private function createPaypalOrderParameter($paymentType)
    {
        $customer = require __DIR__ . '/OrderHandler/_fixtures/b2c_customer.php';
        $cart = require __DIR__ . '/OrderHandler/_fixtures/b2c_basket.php';

        return new PayPalOrderParameter($customer, $cart, $paymentType, null, null);
    }
}
