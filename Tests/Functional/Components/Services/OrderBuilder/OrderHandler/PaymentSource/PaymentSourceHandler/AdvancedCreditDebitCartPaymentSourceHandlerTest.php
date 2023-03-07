<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\OrderBuilder\OrderHandler\PaymentSource\PaymentSourceHandler;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceHandler\AdvancedCreditDebitCardPaymentSourceHandler;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueFactory;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use UnexpectedValueException;

class AdvancedCreditDebitCartPaymentSourceHandlerTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

    /**
     * @return void
     */
    public function testSupports()
    {
        static::assertTrue($this->createAdvancedCreditDebitCardPaymentSourceHandler()->supports(PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD));
        static::assertfalse($this->createAdvancedCreditDebitCardPaymentSourceHandler()->supports(PaymentType::PAYPAL_CLASSIC_V2));
        static::assertfalse($this->createAdvancedCreditDebitCardPaymentSourceHandler()->supports('ANY_OTHER'));
    }

    /**
     * @return void
     */
    public function testCreatePaymentSource()
    {
        $this->insertGeneralSettingsFromArray(['active' => true]);

        $result = $this->createAdvancedCreditDebitCardPaymentSourceHandler()->createPaymentSource(
            $this->createPayPalOrderParameter()
        );

        static::assertInstanceOf(PaymentSource::class, $result);

        $paymentSourceValueResult = $result->getCard();
        static::assertInstanceOf(PaymentSource\Card::class, $paymentSourceValueResult);

        $experienceContextValueResult = $paymentSourceValueResult->getExperienceContext();
        static::assertInstanceOf(PaymentSource\ExperienceContext::class, $experienceContextValueResult);

        static::assertSame('SET_PROVIDED_ADDRESS', $experienceContextValueResult->getShippingPreference());
        static::assertSame('PAY_NOW', $experienceContextValueResult->getUserAction());
    }

    /**
     * @return void
     */
    public function testCreatePaymentSourceShouldThrowException()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Payment source Card expected. Got "NULL"');

        $advancedCreditDebitCardPaymentSourceHandler = new AdvancedCreditDebitCardPaymentSourceHandler(
            $this->createMock(PaymentSourceValueFactory::class)
        );

        $advancedCreditDebitCardPaymentSourceHandler->createPaymentSource($this->createPayPalOrderParameter());
    }

    /**
     * @return AdvancedCreditDebitCardPaymentSourceHandler
     */
    private function createAdvancedCreditDebitCardPaymentSourceHandler()
    {
        return new AdvancedCreditDebitCardPaymentSourceHandler(
            $this->getContainer()->get('paypal_unified.payment_source_value_factory')
        );
    }

    /**
     * @return PayPalOrderParameter
     */
    private function createPayPalOrderParameter()
    {
        return new PayPalOrderParameter(
            require __DIR__ . '/../../_fixtures/b2c_customer.php',
            require __DIR__ . '/../../_fixtures/b2c_basket.php',
            PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD,
            'AnyBasketId',
            null,
            ''
        );
    }
}
