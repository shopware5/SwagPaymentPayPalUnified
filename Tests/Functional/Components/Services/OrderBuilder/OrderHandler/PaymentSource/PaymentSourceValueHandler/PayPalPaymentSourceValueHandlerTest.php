<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\OrderBuilder\OrderHandler\PaymentSource\PaymentSourceValueHandler;

use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueHandler\PayPalPaymentSourceValueHandler;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\ExperienceContext;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayPal;
use SwagPaymentPayPalUnified\Tests\Functional\AssertStringContainsTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use UnexpectedValueException;

class PayPalPaymentSourceValueHandlerTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use AssertStringContainsTrait;

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
        static::assertSame($expectedValue, $this->createPayPalPaymentSourceValueHandler()->supports($paymentType));
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
    public function testCreatePaymentSourceValue()
    {
        $payPalPaymentSourceValueHandler = $this->createPayPalPaymentSourceValueHandler();

        $result = $payPalPaymentSourceValueHandler->createPaymentSourceValue($this->createPayPalOrderParameter(PaymentType::PAYPAL_CLASSIC_V2));

        static::assertInstanceOf(PayPal::class, $result);

        $experienceContextResult = $result->getExperienceContext();
        static::assertInstanceOf(ExperienceContext::class, $experienceContextResult);

        static::assertSame('de-DE', $experienceContextResult->getLocale());
        static::assertSame('anyBrandName', $experienceContextResult->getBrandName());
        static::assertSame('IMMEDIATE_PAYMENT_REQUIRED', $experienceContextResult->getPaymentMethodPreference());
        static::assertSame('PAYPAL', $experienceContextResult->getPaymentMethodSelected());
        static::assertSame('NO_PREFERENCE', $experienceContextResult->getLandingPage());
        static::assertSame('SET_PROVIDED_ADDRESS', $experienceContextResult->getShippingPreference());
        static::assertSame('PAY_NOW', $experienceContextResult->getUserAction());
    }

    /**
     * @return void
     */
    public function testCreatePaymentSourceValuePaymentTypeIsExpress()
    {
        $payPalPaymentSourceValueHandler = $this->createPayPalPaymentSourceValueHandler();

        $result = $payPalPaymentSourceValueHandler->createPaymentSourceValue($this->createPayPalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2));

        static::assertInstanceOf(PayPal::class, $result);

        $experienceContextResult = $result->getExperienceContext();
        static::assertInstanceOf(ExperienceContext::class, $experienceContextResult);

        static::assertSame('de-DE', $experienceContextResult->getLocale());
        static::assertSame('anyBrandName', $experienceContextResult->getBrandName());
        static::assertSame('IMMEDIATE_PAYMENT_REQUIRED', $experienceContextResult->getPaymentMethodPreference());
        static::assertSame('PAYPAL', $experienceContextResult->getPaymentMethodSelected());
        static::assertSame('NO_PREFERENCE', $experienceContextResult->getLandingPage());
        static::assertSame('GET_FROM_FILE', $experienceContextResult->getShippingPreference());
        static::assertSame('CONTINUE', $experienceContextResult->getUserAction());
    }

    /**
     * @return void
     */
    public function testCreatePaymentSourceValueShouldThrowException()
    {
        $payPalPaymentSourceValueHandler = new PayPalPaymentSourceValueHandler(
            $this->createMock(SettingsServiceInterface::class),
            $this->createContextServiceMock(),
            $this->getContainer()->get('paypal_unified.common.return_url_helper')
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expect instance of SwagPaymentPayPalUnified\Models\Settings\General, got NULL');

        $payPalPaymentSourceValueHandler->createPaymentSourceValue($this->createPayPalOrderParameter(PaymentType::PAYPAL_CLASSIC_V2));
    }

    /**
     * @return SettingsServiceInterface&MockObject
     */
    private function createSettingsServiceMock()
    {
        $generalSettingsMock = new General();
        $generalSettingsMock->setShopId('1');
        $generalSettingsMock->setBrandName('anyBrandName');
        $generalSettingsMock->setLandingPageType('NO_PREFERENCE');

        $settingsServiceMock = $this->createMock(SettingsServiceInterface::class);
        $settingsServiceMock->method('getSettings')->willReturn($generalSettingsMock);

        return $settingsServiceMock;
    }

    /**
     * @return PayPalPaymentSourceValueHandler
     */
    private function createPayPalPaymentSourceValueHandler()
    {
        return new PayPalPaymentSourceValueHandler(
            $this->createSettingsServiceMock(),
            $this->createContextServiceMock(),
            $this->getContainer()->get('paypal_unified.common.return_url_helper')
        );
    }

    /**
     * @param PaymentType::* $paymentType
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

    /**
     * @return ContextServiceInterface&MockObject
     */
    private function createContextServiceMock()
    {
        $shopContext = $this->getContainer()->get('shopware_storefront.context_service')->createShopContext(1);
        $contextServiceMock = $this->createMock(ContextServiceInterface::class);
        $contextServiceMock->method('getShopContext')->willReturn($shopContext);

        return $contextServiceMock;
    }
}
