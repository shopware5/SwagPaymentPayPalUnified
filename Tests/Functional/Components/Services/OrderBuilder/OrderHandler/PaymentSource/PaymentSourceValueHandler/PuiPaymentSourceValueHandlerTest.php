<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\OrderBuilder\OrderHandler\PaymentSource\PaymentSourceValueHandler;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueHandler\PuiPaymentSourceValueHandler;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Name;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone\PhoneNumber;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\ExperienceContext;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class PuiPaymentSourceValueHandlerTest extends TestCase
{
    use ContainerTrait;
    use SettingsHelperTrait;
    use ShopRegistrationTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testSupports()
    {
        $puiPaymentSourceValueHandler = $this->createPuiPaymentSourceValueHandler();

        static::assertTrue($puiPaymentSourceValueHandler->supports(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2));
        static::assertFalse($puiPaymentSourceValueHandler->supports('anyOtherPaymentType'));
    }

    /**
     * @return void
     */
    public function testCreatePaymentSourceValue()
    {
        $this->insertGeneralSettingsFromArray(['active' => true]);
        $this->insertPayUponInvoiceSettingsFromArray([
            'onboarding_completed' => true,
            'sandbox_onboarding_completed' => true,
            'active' => true,
            'customer_service_instructions' => 'Customer instructions',
        ]);

        $puiPaymentSourceValueHandler = $this->createPuiPaymentSourceValueHandler();

        $result = $puiPaymentSourceValueHandler->createPaymentSourceValue($this->createPayPalOrderParameter());

        static::assertInstanceOf(PayUponInvoice::class, $result);

        $experienceContextResult = $result->getExperienceContext();
        static::assertInstanceOf(ExperienceContext::class, $experienceContextResult);

        $customerInstructions = $experienceContextResult->getCustomerServiceInstructions()[0];
        static::assertSame('Customer instructions', $customerInstructions);

        $nameResult = $result->getName();
        static::assertInstanceOf(Name::class, $nameResult);

        $phoneNumberResult = $result->getPhone();
        static::assertInstanceOf(PhoneNumber::class, $phoneNumberResult);
        static::assertSame('5555555555', $phoneNumberResult->getNationalNumber());
        static::assertSame('49', $phoneNumberResult->getCountryCode());

        static::assertSame('Max', $nameResult->getGivenName());
        static::assertSame('Mustermann', $nameResult->getSurname());
        static::assertSame('test@example.com', $result->getEmail());
        static::assertSame('2001-01-01', $result->getBirthDate());
    }

    /**
     * @return PuiPaymentSourceValueHandler
     */
    private function createPuiPaymentSourceValueHandler()
    {
        return new PuiPaymentSourceValueHandler(
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('paypal_unified.common.return_url_helper'),
            $this->getContainer()->get('paypal_unified.phone_number_service')
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
            PaymentType::PAYPAL_PAY_UPON_INVOICE_V2,
            'AnyBasketId',
            null,
            ''
        );
    }
}
