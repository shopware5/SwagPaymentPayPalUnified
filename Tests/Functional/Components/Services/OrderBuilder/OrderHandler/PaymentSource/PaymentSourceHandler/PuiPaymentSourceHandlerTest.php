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
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceHandler\PuiPaymentSourceHandler;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueFactory;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueHandler\PuiPaymentSourceValueHandler;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\ExperienceContext;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class PuiPaymentSourceHandlerTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use ShopRegistrationTrait;

    /**
     * @return void
     */
    public function testSupports()
    {
        $puiPaymentSourceHandler = $this->createPuiPaymentSourceHandler();

        static::assertTrue($puiPaymentSourceHandler->supports(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2));
        static::assertFalse($puiPaymentSourceHandler->supports('anyOtherPaymentType'));
    }

    /**
     * @return void
     */
    public function testCreatePaymentSource()
    {
        $this->insertGeneralSettingsFromArray(['active' => true]);
        $this->insertPayUponInvoiceSettingsFromArray([
            'onboarding_completed' => true,
            'sandbox_onboarding_completed' => true,
            'active' => true,
            'customer_service_instructions' => 'Customer instructions',
        ]);

        $result = $this->createPuiPaymentSourceHandler()->createPaymentSource($this->createPayPalOrderParameter());

        static::assertInstanceOf(PaymentSource::class, $result);

        $payUponInvoiceResult = $result->getPayUponInvoice();
        static::assertInstanceOf(PayUponInvoice::class, $payUponInvoiceResult);

        $experienceContextResult = $payUponInvoiceResult->getExperienceContext();
        static::assertInstanceOf(ExperienceContext::class, $experienceContextResult);

        $customerInstructions = $experienceContextResult->getCustomerServiceInstructions()[0];
        static::assertSame('Customer instructions', $customerInstructions);
    }

    /**
     * @return PuiPaymentSourceHandler
     */
    private function createPuiPaymentSourceHandler()
    {
        $paymentSourceValueFactory = new PaymentSourceValueFactory();
        $puiPaymentSourceValueHandler = new PuiPaymentSourceValueHandler(
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('paypal_unified.common.return_url_helper'),
            $this->getContainer()->get('paypal_unified.phone_number_service')
        );

        $paymentSourceValueFactory->addHandler($puiPaymentSourceValueHandler);

        return new PuiPaymentSourceHandler($paymentSourceValueFactory);
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
