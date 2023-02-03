<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\OrderBuilder\OrderHandler\PaymentSource\PaymentSourceHandler;

use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceHandler\PayPalPaymentSourceHandler;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueFactory;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use UnexpectedValueException;

class PayPalPaymentSourceHandlerTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

    /**
     * @dataProvider supportsTestDataProvider
     *
     * @param PaymentType::*|string $paymentType
     * @param bool                  $expectedValue
     *
     * @return void
     */
    public function testSupports($paymentType, $expectedValue)
    {
        static::assertSame($expectedValue, $this->createPayPalPaymentSourceHandler()->supports($paymentType));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function supportsTestDataProvider()
    {
        yield 'PAYPAL_CLASSIC_V2' => [
            PaymentType::PAYPAL_CLASSIC_V2,
            true,
        ];

        yield 'PAYPAL_PAY_LATER' => [
            PaymentType::PAYPAL_PAY_LATER,
            true,
        ];

        yield 'PAYPAL_EXPRESS_V2' => [
            PaymentType::PAYPAL_EXPRESS_V2,
            true,
        ];

        yield 'PAYPAL_SMART_PAYMENT_BUTTONS_V2' => [
            PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2,
            true,
        ];

        yield 'ANY_OTHER' => [
            'ANY_OTHER',
            false,
        ];
    }

    /**
     * @return void
     */
    public function testCreatePaymentSource()
    {
        $this->insertGeneralSettingsFromArray(['active' => true]);

        $result = $this->createPayPalPaymentSourceHandler()->createPaymentSource(
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_CLASSIC_V2)
        );

        static::assertInstanceOf(PaymentSource::class, $result);

        $paymentSourceValueResult = $result->getPaypal();
        static::assertInstanceOf(PaymentSource\PayPal::class, $paymentSourceValueResult);

        $experienceContextValueResult = $paymentSourceValueResult->getExperienceContext();
        static::assertInstanceOf(PaymentSource\ExperienceContext::class, $experienceContextValueResult);

        static::assertSame('SET_PROVIDED_ADDRESS', $experienceContextValueResult->getShippingPreference());
        static::assertSame('PAY_NOW', $experienceContextValueResult->getUserAction());
    }

    /**
     * @return void
     */
    public function testCreatePaymentSourceWithExpress()
    {
        $this->insertGeneralSettingsFromArray(['active' => true]);

        $result = $this->createPayPalPaymentSourceHandler()->createPaymentSource(
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2)
        );

        static::assertInstanceOf(PaymentSource::class, $result);

        $paymentSourceValueResult = $result->getPaypal();
        static::assertInstanceOf(PaymentSource\PayPal::class, $paymentSourceValueResult);

        $experienceContextValueResult = $paymentSourceValueResult->getExperienceContext();
        static::assertInstanceOf(PaymentSource\ExperienceContext::class, $experienceContextValueResult);

        static::assertSame('GET_FROM_FILE', $experienceContextValueResult->getShippingPreference());
        static::assertSame('CONTINUE', $experienceContextValueResult->getUserAction());
    }

    /**
     * @return void
     */
    public function testCreatePaymentSourceShouldThrowException()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Payment source PayPal expected. Got "NULL"');

        $payPayPaymentSourceHandler = new PayPalPaymentSourceHandler(
            $this->createMock(PaymentSourceValueFactory::class)
        );

        $payPayPaymentSourceHandler->createPaymentSource($this->createPayPalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2));
    }

    /**
     * @return PayPalPaymentSourceHandler
     */
    private function createPayPalPaymentSourceHandler()
    {
        return new PayPalPaymentSourceHandler(
            $this->getContainer()->get('paypal_unified.payment_source_value_factory')
        );
    }

    /**
     * @param PaymentType|string $paymentType
     *
     * @return PayPalOrderParameter
     */
    private function createPayPalOrderParameter($paymentType)
    {
        return new PayPalOrderParameter(
            require __DIR__ . '/../../_fixtures/b2c_customer.php',
            require __DIR__ . '/../../_fixtures/b2c_basket.php',
            $paymentType,
            'AnyBasketId',
            null,
            ''
        );
    }
}
