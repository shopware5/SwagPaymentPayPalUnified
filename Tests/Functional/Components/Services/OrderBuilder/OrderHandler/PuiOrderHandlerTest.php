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
use SwagPaymentPayPalUnified\Components\Exception\BirthdateNotValidException;
use SwagPaymentPayPalUnified\Components\Exception\PhoneNumberCountryCodeNotValidException;
use SwagPaymentPayPalUnified\Components\Exception\PhoneNumberNationalNumberNotValidException;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler\PuiOrderHandler;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common\Address;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone\PhoneNumber;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\ExperienceContext;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use UnexpectedValueException;

class PuiOrderHandlerTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use ReflectionHelperTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

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
        $puiOrderHandler = $this->getPuiOrderHandler();

        static::assertSame($expectedResult, $puiOrderHandler->supports($paymentType));
    }

    /**
     * @return Generator<array<int, mixed>>
     */
    public function supportsTestDataProvider()
    {
        yield 'Should return false' => [
            'anyPaymentType',
            false,
        ];

        yield 'Should return true' => [
            PaymentType::PAYPAL_PAY_UPON_INVOICE_V2,
            true,
        ];
    }

    /**
     * @return void
     */
    public function testCreateOrderExpectOrder()
    {
        $this->insertGeneralSettingsFromArray(['active' => true]);
        $this->insertPayUponInvoiceSettingsFromArray([
            'onboarding_completed' => true,
            'sandbox_onboarding_completed' => true,
            'active' => true,
            'customer_service_instructions' => 'Customer instructions',
        ]);

        $puiOrderHandler = $this->getPuiOrderHandler();

        $result = $puiOrderHandler->createOrder($this->createPayPalOrderParameter());

        static::assertInstanceOf(Order::class, $result);

        $paymentSource = $result->getPaymentSource();
        static::assertInstanceOf(PaymentSource::class, $paymentSource);

        $payUponInvoice = $paymentSource->getPayUponInvoice();
        static::assertInstanceOf(PayUponInvoice::class, $payUponInvoice);

        $address = $payUponInvoice->getBillingAddress();
        static::assertInstanceOf(Address::class, $address);

        $experienceContext = $payUponInvoice->getExperienceContext();
        static::assertInstanceOf(ExperienceContext::class, $experienceContext);
        static::assertSame('Customer instructions', $experienceContext->getCustomerServiceInstructions()[0]);
    }

    /**
     * @return void
     */
    public function testValidateOrderExpectUnexpectedValueExceptionBecauseNoPaymentSourceIsset()
    {
        $puiOrderHandler = $this->getPuiOrderHandler();

        $validateOrderReflectionMethod = $this->getReflectionMethod(PuiOrderHandler::class, 'validateOrder');

        $order = new Order();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expect instance of PaymentSource. Got NULL');

        $validateOrderReflectionMethod->invoke($puiOrderHandler, $order);
    }

    /**
     * @return void
     */
    public function testValidateOrderExpectUnexpectedValueExceptionBecauseNoPayUponInvoiceIsset()
    {
        $puiOrderHandler = $this->getPuiOrderHandler();

        $validateOrderReflectionMethod = $this->getReflectionMethod(PuiOrderHandler::class, 'validateOrder');

        $order = new Order();
        $order->setPaymentSource(new PaymentSource());

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expect payment source to be PayUponInvoice. Got NULL');

        $validateOrderReflectionMethod->invoke($puiOrderHandler, $order);
    }

    /**
     * @return void
     */
    public function testValidateOrderExpectUnexpectedValueExceptionBecauseNoPhoneNumberIsset()
    {
        $puiOrderHandler = $this->getPuiOrderHandler();

        $validateOrderReflectionMethod = $this->getReflectionMethod(PuiOrderHandler::class, 'validateOrder');

        $order = new Order();
        $paymentSource = new PaymentSource();
        $payUponInvoice = new PayUponInvoice();
        $paymentSource->setPayUponInvoice($payUponInvoice);
        $order->setPaymentSource($paymentSource);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expect phone number to be PhoneNumber. Got NULL');

        $validateOrderReflectionMethod->invoke($puiOrderHandler, $order);
    }

    /**
     * @return void
     */
    public function testValidateOrderExpectBirthdateNotValidExceptionExceptionBecauseBirthdateIsEmpty()
    {
        $puiOrderHandler = $this->getPuiOrderHandler();

        $validateOrderReflectionMethod = $this->getReflectionMethod(PuiOrderHandler::class, 'validateOrder');

        $order = new Order();
        $paymentSource = new PaymentSource();
        $payUponInvoice = new PayUponInvoice();
        $phoneNumber = new PhoneNumber();
        $payUponInvoice->setPhone($phoneNumber);
        $paymentSource->setPayUponInvoice($payUponInvoice);
        $order->setPaymentSource($paymentSource);

        $this->expectException(BirthdateNotValidException::class);
        $this->expectExceptionMessage('Order does not contain a valid birthdate. Got ');

        $validateOrderReflectionMethod->invoke($puiOrderHandler, $order);
    }

    /**
     * @return void
     */
    public function testValidateOrderExpectPhoneNumberCountryCodeNotValidExceptionBecausePhoneNumberCountryCodeIsEmpty()
    {
        $puiOrderHandler = $this->getPuiOrderHandler();

        $validateOrderReflectionMethod = $this->getReflectionMethod(PuiOrderHandler::class, 'validateOrder');

        $order = new Order();
        $paymentSource = new PaymentSource();
        $payUponInvoice = new PayUponInvoice();
        $payUponInvoice->setBirthDate('2001-01-01');
        $phoneNumber = new PhoneNumber();
        $payUponInvoice->setPhone($phoneNumber);
        $paymentSource->setPayUponInvoice($payUponInvoice);
        $order->setPaymentSource($paymentSource);

        $this->expectException(PhoneNumberCountryCodeNotValidException::class);
        $this->expectExceptionMessage('Expect phone number country code to be 49. Got ');

        $validateOrderReflectionMethod->invoke($puiOrderHandler, $order);
    }

    /**
     * @return void
     */
    public function testValidateOrderExpectPhoneNumberCountryCodeNotValidExceptionBecausePhoneNumberCountryCodeIsInvalid()
    {
        $puiOrderHandler = $this->getPuiOrderHandler();

        $validateOrderReflectionMethod = $this->getReflectionMethod(PuiOrderHandler::class, 'validateOrder');

        $order = new Order();
        $paymentSource = new PaymentSource();
        $payUponInvoice = new PayUponInvoice();
        $payUponInvoice->setBirthDate('2001-01-01');
        $phoneNumber = new PhoneNumber();
        $phoneNumber->setCountryCode('1');
        $payUponInvoice->setPhone($phoneNumber);
        $paymentSource->setPayUponInvoice($payUponInvoice);
        $order->setPaymentSource($paymentSource);

        $this->expectException(PhoneNumberCountryCodeNotValidException::class);
        $this->expectExceptionMessage('Expect phone number country code to be 49. Got 1');

        $validateOrderReflectionMethod->invoke($puiOrderHandler, $order);
    }

    /**
     * @return void
     */
    public function testValidateOrderExpectPhoneNumberNationalNumberNotValidExceptionBecausePhoneNumberIsNull()
    {
        $puiOrderHandler = $this->getPuiOrderHandler();

        $validateOrderReflectionMethod = $this->getReflectionMethod(PuiOrderHandler::class, 'validateOrder');

        $order = new Order();
        $paymentSource = new PaymentSource();
        $payUponInvoice = new PayUponInvoice();
        $payUponInvoice->setBirthDate('2001-01-01');
        $phoneNumber = new PhoneNumber();
        $phoneNumber->setCountryCode('49');
        $payUponInvoice->setPhone($phoneNumber);
        $paymentSource->setPayUponInvoice($payUponInvoice);
        $order->setPaymentSource($paymentSource);

        $this->expectException(PhoneNumberNationalNumberNotValidException::class);
        $this->expectExceptionMessage('Expect phone number. Got ');

        $validateOrderReflectionMethod->invoke($puiOrderHandler, $order);
    }

    /**
     * @return void
     */
    public function testValidateOrderExpectPhoneNumberNationalNumberNotValidExceptionBecausePhoneNumberIsInvalid()
    {
        $puiOrderHandler = $this->getPuiOrderHandler();

        $validateOrderReflectionMethod = $this->getReflectionMethod(PuiOrderHandler::class, 'validateOrder');

        $order = new Order();
        $paymentSource = new PaymentSource();
        $payUponInvoice = new PayUponInvoice();
        $payUponInvoice->setBirthDate('2001-01-01');
        $phoneNumber = new PhoneNumber();
        $phoneNumber->setCountryCode('49');
        $phoneNumber->setNationalNumber('0as5321546');
        $payUponInvoice->setPhone($phoneNumber);
        $paymentSource->setPayUponInvoice($payUponInvoice);
        $order->setPaymentSource($paymentSource);

        $this->expectException(PhoneNumberNationalNumberNotValidException::class);
        $this->expectExceptionMessage('Expect phone number. Got 0as5321546');

        $validateOrderReflectionMethod->invoke($puiOrderHandler, $order);
    }

    /**
     * @dataProvider isBirthdayValidTestDataProvider
     *
     * @param string|null $birthdate
     * @param bool        $expectedResult
     *
     * @return void
     */
    public function testIsBirthdayValid($birthdate, $expectedResult)
    {
        $puiOrderHandler = $this->getPuiOrderHandler();

        $validateOrderReflectionMethod = $this->getReflectionMethod(PuiOrderHandler::class, 'isBirthdayValid');

        static::assertSame($expectedResult, $validateOrderReflectionMethod->invoke($puiOrderHandler, $birthdate));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function isBirthdayValidTestDataProvider()
    {
        yield 'Birthdate is not a string' => [
            null,
            false,
        ];

        yield 'Birthdate is invalid date string' => [
            '2001-0A-bb',
            false,
        ];

        yield 'Birthdate is valid' => [
            '2001-01-01',
            true,
        ];
    }

    /**
     * @return PuiOrderHandler
     */
    private function getPuiOrderHandler()
    {
        $puiOrderHandler = $this->getContainer()->get('paypal_unified.pui_order_factory_handler');

        static::assertInstanceOf(PuiOrderHandler::class, $puiOrderHandler);

        return $puiOrderHandler;
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
            PaymentType::PAYPAL_PAY_UPON_INVOICE_V2,
            'basketUniqueId',
            'paymentToken',
            '100178'
        );
    }
}
