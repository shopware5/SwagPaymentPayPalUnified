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
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler\ClassicOrderHandler;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\Tests\Functional\AssertStringContainsTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class ClassicOrderHandlerTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use SettingsHelperTrait;
    use DatabaseTestCaseTrait;
    use AssertStringContainsTrait;

    /**
     * @dataProvider supportsTestDateProvider
     *
     * @param PaymentType::*|string $paymentType
     * @param bool                  $expectedResult
     *
     * @return void
     */
    public function testSupports($paymentType, $expectedResult)
    {
        $classicOrderHandler = $this->createClassicOrderHandler();

        static::assertSame($expectedResult, $classicOrderHandler->supports($paymentType));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function supportsTestDateProvider()
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

        yield 'PAYPAL_ADVANCED_CREDIT_DEBIT_CARD' => [
            PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD,
            true,
        ];

        yield 'ANY_OTHER' => [
            'ANY_OTHER',
            false,
        ];
    }

    /**
     * @dataProvider CreateOrderForClassicPayLaterExpressSmartPaymentButtonsTestDataProvider
     *
     * @param PaymentType::*|string $paymentType
     *
     * @return void
     */
    public function testCreateOrderForClassicPayLaterAndSmartPaymentButtons($paymentType)
    {
        $this->insertGeneralSettingsFromArray(['active' => true, 'brandName' => 'UnitTest AG']);

        $result = $this->createClassicOrderHandler()->createOrder(
            $this->createPayPalOrderParameter($paymentType)
        );

        static::assertSame('CAPTURE', $result->getIntent());
        static::assertCount(1, $result->getPurchaseUnits());

        $purchaseUnit = $result->getPurchaseUnits()[0];
        static::assertInstanceOf(PurchaseUnit::class, $purchaseUnit);
        static::assertInstanceOf(PurchaseUnit\Amount::class, $purchaseUnit->getAmount());

        $shipping = $purchaseUnit->getShipping();
        static::assertInstanceOf(PurchaseUnit\Shipping::class, $shipping);
        static::assertInstanceOf(PurchaseUnit\Shipping\Address::class, $shipping->getAddress());

        $paymentSource = $result->getPaymentSource();
        static::assertInstanceOf(PaymentSource::class, $paymentSource);
        static::assertInstanceOf(PaymentSource\PayPal::class, $paymentSource->getPaypal());
        $experienceContext = $paymentSource->getPaypal()->getExperienceContext();
        static::assertInstanceOf(PaymentSource\ExperienceContext::class, $experienceContext);

        static::assertSame('de-DE', $experienceContext->getLocale());
        static::assertSame('UnitTest AG', $experienceContext->getBrandName());
        static::assertSame('IMMEDIATE_PAYMENT_REQUIRED', $experienceContext->getPaymentMethodPreference());
        static::assertSame('PAYPAL', $experienceContext->getPaymentMethodSelected());

        static::assertSame('PAY_NOW', $experienceContext->getUserAction());
        static::assertSame('SET_PROVIDED_ADDRESS', $experienceContext->getShippingPreference());
    }

    /**
     * @return Generator<array<int,string>>
     */
    public function CreateOrderForClassicPayLaterExpressSmartPaymentButtonsTestDataProvider()
    {
        yield 'PAYPAL_CLASSIC_V2' => [
            PaymentType::PAYPAL_CLASSIC_V2,
        ];

        yield 'PAYPAL_PAY_LATER' => [
            PaymentType::PAYPAL_PAY_LATER,
        ];

        yield 'PAYPAL_SMART_PAYMENT_BUTTONS_V2' => [
            PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2,
        ];
    }

    /**
     * @return void
     */
    public function testCreateOrderForExpress()
    {
        $this->insertGeneralSettingsFromArray(['active' => true, 'brandName' => 'UnitTest AG']);

        $result = $this->createClassicOrderHandler()->createOrder(
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2)
        );

        static::assertSame('CAPTURE', $result->getIntent());
        static::assertCount(1, $result->getPurchaseUnits());

        $purchaseUnit = $result->getPurchaseUnits()[0];
        static::assertInstanceOf(PurchaseUnit::class, $purchaseUnit);
        static::assertInstanceOf(PurchaseUnit\Amount::class, $purchaseUnit->getAmount());

        $paymentSource = $result->getPaymentSource();
        static::assertInstanceOf(PaymentSource::class, $paymentSource);
        static::assertInstanceOf(PaymentSource\PayPal::class, $paymentSource->getPaypal());
        $experienceContext = $paymentSource->getPaypal()->getExperienceContext();
        static::assertInstanceOf(PaymentSource\ExperienceContext::class, $experienceContext);

        static::assertSame('de-DE', $experienceContext->getLocale());
        static::assertSame('UnitTest AG', $experienceContext->getBrandName());
        static::assertSame('IMMEDIATE_PAYMENT_REQUIRED', $experienceContext->getPaymentMethodPreference());
        static::assertSame('PAYPAL', $experienceContext->getPaymentMethodSelected());

        static::assertSame('CONTINUE', $experienceContext->getUserAction());
        static::assertSame('GET_FROM_FILE', $experienceContext->getShippingPreference());
    }

    /**
     * @return void
     */
    public function testCreateOrderForAdvancedCreditDebitCard()
    {
        $this->insertGeneralSettingsFromArray(['active' => true, 'brandName' => 'UnitTest AG']);

        $result = $this->createClassicOrderHandler()->createOrder(
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD)
        );

        static::assertSame('CAPTURE', $result->getIntent());
        static::assertCount(1, $result->getPurchaseUnits());

        $purchaseUnit = $result->getPurchaseUnits()[0];
        static::assertInstanceOf(PurchaseUnit::class, $purchaseUnit);
        static::assertInstanceOf(PurchaseUnit\Amount::class, $purchaseUnit->getAmount());

        $shipping = $purchaseUnit->getShipping();
        static::assertInstanceOf(PurchaseUnit\Shipping::class, $shipping);
        static::assertInstanceOf(PurchaseUnit\Shipping\Address::class, $shipping->getAddress());

        $paymentSource = $result->getPaymentSource();
        static::assertInstanceOf(PaymentSource::class, $paymentSource);
        static::assertInstanceOf(PaymentSource\Card::class, $paymentSource->getCard());
        static::assertNull($paymentSource->getCard()->getExperienceContext());
    }

    /**
     * @return ClassicOrderHandler
     */
    private function createClassicOrderHandler()
    {
        return new ClassicOrderHandler(
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('paypal_unified.paypal_order.item_list_provider'),
            $this->getContainer()->get('paypal_unified.paypal_order.amount_provider'),
            $this->getContainer()->get('paypal_unified.common.return_url_helper'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            new PriceFormatter(),
            new CustomerHelper(),
            $this->getContainer()->get('paypal_unified.payment_source_factory'),
            $this->getContainer()->get('snippets')
        );
    }

    /**
     * @param PaymentType::*|string $paymentType
     *
     * @return PayPalOrderParameter
     */
    private function createPayPalOrderParameter($paymentType)
    {
        return new PayPalOrderParameter(
            require __DIR__ . '/_fixtures/b2c_customer.php',
            require __DIR__ . '/_fixtures/b2c_basket.php',
            $paymentType,
            'AnyBasketId',
            null,
            ''
        );
    }
}
