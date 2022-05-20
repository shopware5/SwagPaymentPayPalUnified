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
use SwagPaymentPayPalUnified\Components\Services\Common\CartHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\AmountProvider;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\ItemTotal;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\Shipping;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\TaxTotal;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\Tax;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\UnitAmount;

class AmountProviderTest extends TestCase
{
    /**
     * @dataProvider createBreakdownTestDataProvider
     *
     * @param string                   $currencyCode
     * @param array<string,mixed>      $cart
     * @param array<string,mixed>      $customer
     * @param array<string,mixed>|null $expectedResult
     *
     * @return void
     */
    public function testCreateBreakdown(PurchaseUnit $purchaseUnit, $currencyCode, array $cart, array $customer, $expectedResult)
    {
        $amountProvider = $this->createAmountProvider();

        $reflectionMethod = (new ReflectionClass(AmountProvider::class))->getMethod('createBreakdown');
        $reflectionMethod->setAccessible(true);

        /** @var Breakdown|null $result */
        $result = $reflectionMethod->invokeArgs($amountProvider, [$purchaseUnit, $currencyCode, $cart, $customer]);

        if ($expectedResult === null) {
            static::assertNull($result);

            return;
        }

        static::assertInstanceOf(Breakdown::class, $result);

        static::assertNotNull($expectedResult);
        static::assertArrayHasKey('itemTotal', $expectedResult);

        static::assertInstanceOf(ItemTotal::class, $expectedResult['itemTotal']);
        static::assertInstanceOf(ItemTotal::class, $result->getItemTotal());
        static::assertSame($expectedResult['itemTotal']->getCurrencyCode(), $result->getItemTotal()->getCurrencyCode());
        static::assertSame($expectedResult['itemTotal']->getValue(), $result->getItemTotal()->getValue());

        static::assertInstanceOf(Shipping::class, $expectedResult['shipping']);
        static::assertInstanceOf(Shipping::class, $result->getShipping());
        static::assertSame($expectedResult['shipping']->getCurrencyCode(), $result->getShipping()->getCurrencyCode());
        static::assertSame($expectedResult['shipping']->getValue(), $result->getShipping()->getValue());

        if ($expectedResult['taxTotal'] !== null) {
            static::assertInstanceOf(TaxTotal::class, $expectedResult['taxTotal']);
            static::assertInstanceOf(TaxTotal::class, $result->getTaxTotal());
            static::assertSame($expectedResult['taxTotal']->getCurrencyCode(), $result->getTaxTotal()->getCurrencyCode());
            static::assertSame($expectedResult['taxTotal']->getValue(), $result->getTaxTotal()->getValue());
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function createBreakdownTestDataProvider()
    {
        yield 'Items in PurchaseUnit is null. Expected null' => [
            new PurchaseUnit(),
            'EUR',
            [],
            [],
            null,
        ];

        yield 'Items in PurchaseUnit has Tax. Expect Breakdown with tax' => [
            $this->createPurchaseUnitWithItemsWithTax(),
            'EUR',
            $this->getB2CBasketWithThreeItems(),
            $this->getCustomer(),
            $this->getExpectedResultForB2C(),
        ];

        yield 'Items in PurchaseUnit has Tax but the customer is B2B. Expect Breakdown with tax' => [
            $this->createB2BPurchaseUnitWithItemsWithTax(),
            'EUR',
            $this->getB2BBasketWithThreeItemsAndTax(),
            $this->getCustomerWithNetPrices(),
            $this->getExpectedResultForB2B(),
        ];

        yield 'Items in PurchaseUnit has no Tax with B2B customer. Expect Breakdown without tax' => [
            $this->createB2BPurchaseUnitWithItemsWithoutTax(),
            'EUR',
            $this->getB2BBasketWithThreeItemsWithoutTax(),
            $this->getCustomerWithoutVat(),
            $this->getExpectedResultForB2BWithoutTax(),
        ];
    }

    /**
     * @return array<string,ItemTotal|TaxTotal|Shipping|null>
     */
    private function getExpectedResultForB2C()
    {
        $itemTotal = new ItemTotal();
        $itemTotal->setCurrencyCode('EUR');
        $itemTotal->setValue('300.00');

        $taxTotal = new TaxTotal();
        $taxTotal->setCurrencyCode('EUR');
        $taxTotal->setValue('47.91');

        $shipping = new Shipping();
        $shipping->setCurrencyCode('EUR');
        $shipping->setValue('3.90');

        return [
            'itemTotal' => $itemTotal,
            'taxTotal' => $taxTotal,
            'shipping' => $shipping,
        ];
    }

    /**
     * @return array<string,ItemTotal|TaxTotal|Shipping|null>
     */
    private function getExpectedResultForB2B()
    {
        $itemTotal = new ItemTotal();
        $itemTotal->setCurrencyCode('EUR');
        $itemTotal->setValue('252.09');

        $taxTotal = new TaxTotal();
        $taxTotal->setCurrencyCode('EUR');
        $taxTotal->setValue('47.91');

        $shipping = new Shipping();
        $shipping->setCurrencyCode('EUR');
        $shipping->setValue('3.90');

        return [
            'itemTotal' => $itemTotal,
            'taxTotal' => $taxTotal,
            'shipping' => $shipping,
        ];
    }

    /**
     * @return array<string,ItemTotal|TaxTotal|Shipping|null>
     */
    private function getExpectedResultForB2BWithoutTax()
    {
        $itemTotal = new ItemTotal();
        $itemTotal->setCurrencyCode('EUR');
        $itemTotal->setValue('252.09');

        $shipping = new Shipping();
        $shipping->setCurrencyCode('EUR');
        $shipping->setValue('3.28');

        return [
            'itemTotal' => $itemTotal,
            'taxTotal' => null,
            'shipping' => $shipping,
        ];
    }

    /**
     * @return PurchaseUnit
     */
    private function createPurchaseUnitWithItemsWithTax()
    {
        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setItems([
            $this->createItem('100.00', 'Foo', 'Bar', true),
            $this->createItem('100.00', 'Bar', 'Foo', true),
            $this->createItem('100.00', 'FooBar', 'FooBar', true),
        ]);

        return $purchaseUnit;
    }

    /**
     * @return PurchaseUnit
     */
    private function createB2BPurchaseUnitWithItemsWithTax()
    {
        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setItems([
            $this->createItem('84.03', 'Foo', 'Bar', true),
            $this->createItem('84.03', 'Bar', 'Foo', true),
            $this->createItem('84.03', 'FooBar', 'FooBar', true),
        ]);

        return $purchaseUnit;
    }

    /**
     * @return PurchaseUnit
     */
    private function createB2BPurchaseUnitWithItemsWithoutTax()
    {
        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setItems([
            $this->createItem('84.03', 'Foo', 'Bar', false),
            $this->createItem('84.03', 'Bar', 'Foo', false),
            $this->createItem('84.03', 'FooBar', 'FooBar', false),
        ]);

        return $purchaseUnit;
    }

    /**
     * @param string $value
     * @param string $name
     * @param string $ordernumber
     * @param bool   $hasTax
     *
     * @return Item
     */
    private function createItem($value, $name, $ordernumber, $hasTax)
    {
        $tax = new Tax();
        $tax->setValue('15.97');
        $tax->setCurrencyCode('EUR');

        $unitAmount = new UnitAmount();
        $unitAmount->setCurrencyCode('EUR');
        $unitAmount->setValue($value);

        $item = new Item();
        $item->setName($name);
        $item->setSku($ordernumber);
        $item->setQuantity(1);
        $item->setUnitAmount($unitAmount);

        if ($hasTax) {
            $item->setTaxRate('19.00');
            $item->setTax($tax);
        }

        return $item;
    }

    /**
     * @return array<string,mixed>
     */
    private function getB2CBasketWithThreeItems()
    {
        return require __DIR__ . '/_fixtures/b2c_basket_with_three_items_and_full_tax.php';
    }

    /**
     * @return array<string,mixed>
     */
    private function getB2BBasketWithThreeItemsAndTax()
    {
        return require __DIR__ . '/_fixtures/b2b_basket_with_three_items_and_tax.php';
    }

    /**
     * @return array<string,mixed>
     */
    private function getB2BBasketWithThreeItemsWithoutTax()
    {
        return require __DIR__ . '/_fixtures/b2b_basket_with_three_items_and_tax.php';
    }

    /**
     * @return array<string,mixed>
     */
    private function getCustomer()
    {
        return require __DIR__ . '/_fixtures/b2c_customer.php';
    }

    /**
     * @return array<string,mixed>
     */
    private function getCustomerWithNetPrices()
    {
        $customer = require __DIR__ . '/_fixtures/b2c_customer.php';
        $customer['additional']['charge_vat'] = true;
        $customer['additional']['show_net'] = true;

        return $customer;
    }

    /**
     * @return array<string,mixed>
     */
    private function getCustomerWithoutVat()
    {
        $customer = require __DIR__ . '/_fixtures/b2c_customer.php';
        $customer['additional']['charge_vat'] = false;
        $customer['additional']['show_net'] = false;

        return $customer;
    }

    /**
     * @return AmountProvider
     */
    private function createAmountProvider()
    {
        $customerHelper = new CustomerHelper();
        $priceFormatter = new PriceFormatter();

        return new AmountProvider(
            new CartHelper($customerHelper, $priceFormatter),
            $customerHelper,
            $priceFormatter
        );
    }
}
