<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\OrderBuilder\OrderHandler\PaymentSource\PaymentSourceValueHandler;

use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueHandler\AbstractPaymentSourceValueHandler;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueHandler\GiropayPaymentSourceValueHandler;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\ExperienceContext;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class AbstractPaymentSourceValueHandlerTest extends TestCase
{
    use ReflectionHelperTrait;
    use ContainerTrait;
    use ShopRegistrationTrait;
    use SettingsHelperTrait;
    use DatabaseTestCaseTrait;

    /**
     * @dataProvider shortensBrandNameTestDataProvider
     *
     * @param string $brandName
     * @param int    $expectedStringLength
     * @param string $expectedStringResult
     *
     * @return void
     */
    public function testShortensBrandName($brandName, $expectedStringLength, $expectedStringResult)
    {
        $paymentSourceValueHandler = new GiropayPaymentSourceValueHandler(
            $this->createMock(SettingsServiceInterface::class),
            $this->createMock(ContextServiceInterface::class),
            $this->createMock(ReturnUrlHelper::class)
        );

        $reflectionMethod = $this->getReflectionMethod(GiropayPaymentSourceValueHandler::class, 'shortensBrandName');

        $result = $reflectionMethod->invoke($paymentSourceValueHandler, $brandName);

        static::assertSame($expectedStringLength, \strlen($result));
        static::assertSame($expectedStringResult, $result);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function shortensBrandNameTestDataProvider()
    {
        yield 'A brand name with normal length' => [
            'A brand name with normal length',
            \strlen('A brand name with normal length'),
            'A brand name with normal length',
        ];

        yield 'A long brand name with 127 characters' => [
            'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliqu',
            127,
            'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliqu',
        ];

        yield 'A long brand name with 130 characters' => [
            'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam',
            127,
            'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliqu',
        ];

        yield 'A very long brand name with 300 characters' => [
            'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lore',
            127,
            'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliqu',
        ];
    }

    /**
     * @dataProvider createExperienceContextTestDataProvider
     *
     * @return void
     */
    public function testCreateExperienceContext(
        AbstractPaymentSourceValueHandler $orderValueHandler,
        PayPalOrderParameter $orderParameter
    ) {
        $this->insertGeneralSettingsFromArray([
            'active' => true,
            'brand_name' => 'AnyFancyFooBarBrandName',
        ]);

        $reflectionMethod = $this->getReflectionMethod(\get_class($orderValueHandler), 'createExperienceContext');

        $result = $reflectionMethod->invoke($orderValueHandler, $orderParameter);

        static::assertInstanceOf(ExperienceContext::class, $result);

        static::assertSame('AnyFancyFooBarBrandName', $result->getBrandName());
        static::assertSame(ExperienceContext::PAYMENT_METHOD_PREFERENCE, $result->getPaymentMethodPreference());
        static::assertSame(ExperienceContext::PAYMENT_METHOD, $result->getPaymentMethodSelected());
        static::assertSame(ExperienceContext::SHIPPING_PREFERENCE_PROVIDED_ADDRESS, $result->getShippingPreference());
        static::assertSame(ExperienceContext::USER_ACTION_PAY_NOW, $result->getUserAction());
        static::assertSame('de-DE', $result->getLocale());
        // check default landing page isset
        static::assertSame('NO_PREFERENCE', $result->getLandingPage());
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function createExperienceContextTestDataProvider()
    {
        yield 'PAYPAL_ADVANCED_CREDIT_DEBIT_CARD' => [
            $this->getContainer()->get('paypal_unified.advanced_credit_debit_card_payment_source_value_handler'),
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD),
        ];

        yield 'APM_BANCONTACT' => [
            $this->getContainer()->get('paypal_unified.bancontact_payment_source_value_handler'),
            $this->createPayPalOrderParameter(PaymentType::APM_BANCONTACT),
        ];

        yield 'APM_BLIK' => [
            $this->getContainer()->get('paypal_unified.blik_payment_source_value_handler'),
            $this->createPayPalOrderParameter(PaymentType::APM_BLIK),
        ];

        yield 'APM_EPS' => [
            $this->getContainer()->get('paypal_unified.eps_payment_source_value_handler'),
            $this->createPayPalOrderParameter(PaymentType::APM_EPS),
        ];

        yield 'APM_GIROPAY' => [
            $this->getContainer()->get('paypal_unified.giropay_payment_source_value_handler'),
            $this->createPayPalOrderParameter(PaymentType::APM_GIROPAY),
        ];

        yield 'APM_IDEAL' => [
            $this->getContainer()->get('paypal_unified.ideal_payment_source_value_handler'),
            $this->createPayPalOrderParameter(PaymentType::APM_IDEAL),
        ];

        yield 'APM_MULTIBANCO' => [
            $this->getContainer()->get('paypal_unified.multibanco_payment_source_value_handler'),
            $this->createPayPalOrderParameter(PaymentType::APM_MULTIBANCO),
        ];

        yield 'APM_MYBANK' => [
            $this->getContainer()->get('paypal_unified.mybank_payment_source_value_handler'),
            $this->createPayPalOrderParameter(PaymentType::APM_MYBANK),
        ];

        yield 'APM_P24' => [
            $this->getContainer()->get('paypal_unified.p24_payment_source_value_handler'),
            $this->createPayPalOrderParameter(PaymentType::APM_P24),
        ];

        yield 'PAYPAL_CLASSIC_V2' => [
            $this->getContainer()->get('paypal_unified.pay_pal_payment_source_value_handler'),
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_CLASSIC_V2),
        ];

        yield 'APM_SOFORT' => [
            $this->getContainer()->get('paypal_unified.sofort_payment_source_value_handler'),
            $this->createPayPalOrderParameter(PaymentType::APM_SOFORT),
        ];

        yield 'APM_TRUSTLY' => [
            $this->getContainer()->get('paypal_unified.trustly_payment_source_value_handler'),
            $this->createPayPalOrderParameter(PaymentType::APM_TRUSTLY),
        ];
    }

    /**
     * @param PaymentType::* $paymentType
     *
     * @return PayPalOrderParameter
     */
    private function createPayPalOrderParameter($paymentType)
    {
        return new PayPalOrderParameter(
            [],
            [],
            $paymentType,
            '',
            null,
            ''
        );
    }
}
