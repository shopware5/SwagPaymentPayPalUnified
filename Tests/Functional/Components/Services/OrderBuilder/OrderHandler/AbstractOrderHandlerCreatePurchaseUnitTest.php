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
use ReflectionClass;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\AmountProvider;
use SwagPaymentPayPalUnified\Components\Services\PhoneNumberBuilder;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;

class AbstractOrderHandlerCreatePurchaseUnitTest extends TestCase
{
    use ContainerTrait;

    /**
     * @dataProvider createPurchaseUnitsTestDataProvider
     *
     * @param bool $submitCart
     * @param bool $shouldHaveAddress
     * @param bool $shouldHaveAItemList
     *
     * @return void
     */
    public function testCreatePurchaseUnits(PayPalOrderParameter $paypalOrderParameter, $submitCart, $shouldHaveAddress, $shouldHaveAItemList)
    {
        $abstractOrderHandler = $this->createAbstractOrderHandler($submitCart);

        $reflectionClass = new ReflectionClass(OrderHandlerMock::class);
        $reflectionMethod = $reflectionClass->getMethod('createPurchaseUnits');
        $reflectionMethod->setAccessible(true);

        /** @var array<PurchaseUnit> $result */
        $result = $reflectionMethod->invoke($abstractOrderHandler, $paypalOrderParameter);

        if ($shouldHaveAddress) {
            static::assertNotEmpty($result[0]->getShipping());
        } else {
            static::assertNull($result[0]->getShipping());
        }

        if ($shouldHaveAItemList) {
            static::assertNotEmpty($result[0]->getItems());
        } else {
            static::assertNull($result[0]->getItems());
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function createPurchaseUnitsTestDataProvider()
    {
        yield 'With PaymentType::PAYPAL_EXPRESS_V2' => [
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2),
            false,
            false,
            false,
        ];

        yield 'With PaymentType::PAYPAL_EXPRESS_V2 and submit cart' => [
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2),
            true,
            false,
            true,
        ];

        yield 'With PaymentType::PAYPAL_CLASSIC_V2' => [
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_CLASSIC_V2),
            false,
            true,
            false,
        ];

        yield 'With PaymentType::PAYPAL_CLASSIC_V2 and submit cart' => [
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_CLASSIC_V2),
            true,
            true,
            true,
        ];

        yield 'With PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD' => [
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD),
            false,
            true,
            false,
        ];

        yield 'With PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD and submit cart' => [
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD),
            true,
            true,
            true,
        ];

        yield 'With PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2' => [
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2),
            false,
            true,
            false,
        ];

        yield 'With PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2 and submit cart' => [
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2),
            true,
            true,
            true,
        ];

        yield 'With PaymentType::PAYPAL_PAY_UPON_INVOICE_V2' => [
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2),
            false,
            true,
            true,
        ];

        yield 'With PaymentType::PAYPAL_PAY_UPON_INVOICE_V2 and submit cart' => [
            $this->createPayPalOrderParameter(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2),
            true,
            true,
            true,
        ];
    }

    /**
     * @param bool $submitCart
     *
     * @return OrderHandlerMock
     */
    private function createAbstractOrderHandler($submitCart)
    {
        return new OrderHandlerMock(
            $this->createSettingsService($submitCart),
            $this->getContainer()->get('paypal_unified.paypal_order.item_list_provider'),
            $this->createMock(AmountProvider::class),
            $this->createMock(ReturnUrlHelper::class),
            $this->createMock(ContextService::class),
            $this->createMock(PhoneNumberBuilder::class),
            $this->createMock(PriceFormatter::class)
        );
    }

    /**
     * @param PaymentType::* $paymentType
     * @param bool           $useB2BCustomer
     *
     * @return PayPalOrderParameter
     */
    private function createPayPalOrderParameter($paymentType, $useB2BCustomer = false)
    {
        if ($useB2BCustomer) {
            $customerData = require __DIR__ . '/_fixtures/b2b_customer.php';
            $basketData = require __DIR__ . '/_fixtures/b2b_basket.php';
        } else {
            $customerData = require __DIR__ . '/_fixtures/b2c_customer.php';
            $basketData = require __DIR__ . '/_fixtures/b2c_basket.php';
        }

        return new PayPalOrderParameter(
            $customerData,
            $basketData,
            $paymentType,
            'basketUniqueId',
            'paymentToken'
        );
    }

    /**
     * @param bool $submitCart
     *
     * @return SettingsService
     */
    private function createSettingsService($submitCart = false)
    {
        $settingsService = $this->createMock(SettingsService::class);

        if ($submitCart) {
            $settingsService->method('get')->willReturn(true);
        }

        return $settingsService;
    }
}
