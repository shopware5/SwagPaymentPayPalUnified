<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\OrderBuilder\OrderHandler;

use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler\ApmOrderHandler;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\ProcessingInstruction;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class ApmOrderHandlerTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;

    /**
     * @return void
     */
    public function testCreateOrderShouldSetProcessingInstruction()
    {
        $result = $this->getApmOrderHandler()->createOrder($this->createPayPalOrderParameter());

        static::assertInstanceOf(Order::class, $result);
        static::assertSame(ProcessingInstruction::ORDER_COMPLETE_ON_PAYMENT_APPROVAL, $result->getProcessingInstruction());
    }

    /**
     * @dataProvider supportsTestDataProvider
     *
     * @param string $paymentType
     * @param bool   $expectedResult
     *
     * @return void
     */
    public function testSupports($paymentType, $expectedResult)
    {
        static::assertSame($expectedResult, $this->getApmOrderHandler()->supports($paymentType));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function supportsTestDataProvider()
    {
        yield 'Should support APM_BANCONTACT' => [
            PaymentType::APM_BANCONTACT,
            true,
        ];

        yield 'Should support APM_BLIK' => [
            PaymentType::APM_BLIK,
            true,
        ];

        yield 'Should support APM_EPS' => [
            PaymentType::APM_EPS,
            true,
        ];

        yield 'Should support APM_GIROPAY' => [
            PaymentType::APM_GIROPAY,
            true,
        ];

        yield 'Should support APM_IDEAL' => [
            PaymentType::APM_IDEAL,
            true,
        ];

        yield 'Should support APM_MYBANK' => [
            PaymentType::APM_MYBANK,
            true,
        ];

        yield 'Should support APM_P24' => [
            PaymentType::APM_P24,
            true,
        ];

        yield 'Should support APM_SOFORT' => [
            PaymentType::APM_SOFORT,
            true,
        ];

        yield 'Should support APM_TRUSTLY' => [
            PaymentType::APM_TRUSTLY,
            true,
        ];

        yield 'Should support APM_MULTIBANCO' => [
            PaymentType::APM_MULTIBANCO,
            true,
        ];

        yield 'Should not support PAYPAL_PAY_UPON_INVOICE_V2' => [
            PaymentType::PAYPAL_PAY_UPON_INVOICE_V2,
            false,
        ];

        yield 'Should not support anyOtherPaymentType' => [
            'anyOtherPaymentType',
            false,
        ];
    }

    /**
     * @return ApmOrderHandler
     */
    private function getApmOrderHandler()
    {
        $orderHandler = $this->getContainer()->get('paypal_unified.apm_order_factory_handler');

        static::assertInstanceOf(ApmOrderHandler::class, $orderHandler);

        return $orderHandler;
    }

    /**
     * @return PayPalOrderParameter
     */
    private function createPayPalOrderParameter()
    {
        $customerData = require __DIR__ . '/_fixtures/b2c_customer.php';
        $basketData = require __DIR__ . '/_fixtures/b2c_basket.php';

        return new PayPalOrderParameter(
            $customerData,
            $basketData,
            PaymentType::APM_GIROPAY,
            'basketUniqueId',
            null,
            '100178'
        );
    }
}
