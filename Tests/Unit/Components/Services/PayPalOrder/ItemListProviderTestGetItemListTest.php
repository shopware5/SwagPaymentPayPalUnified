<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Services\PayPalOrder;

use Enlight_Components_Snippet_Namespace;
use Generator;
use PHPUnit\Framework\TestCase;
use Shopware_Components_Snippet_Manager;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\LoggerService;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\ItemListProvider;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\Tax;

class ItemListProviderTestGetItemListTest extends TestCase
{
    /**
     * @dataProvider getItemListTestDataProvider
     *
     * @param array<string,mixed> $cart
     * @param array<string,mixed> $customer
     * @param PaymentType::*      $paymentType
     * @param bool                $expectLineItemHasTax
     * @param string              $expectedTaxValue
     * @param string              $expectedUnitAmountValue
     *
     * @return void
     */
    public function testGetItemList(array $cart, array $customer, $paymentType, $expectLineItemHasTax, $expectedTaxValue, $expectedUnitAmountValue)
    {
        $itemListProvider = $this->createItemListProvider();
        $result = $itemListProvider->getItemList($cart, $customer, $paymentType);
        static::assertNotNull($result);

        if ($expectLineItemHasTax) {
            foreach ($result as $lineItem) {
                static::assertInstanceOf(Item::class, $lineItem);
                static::assertInstanceOf(Tax::class, $lineItem->getTax());
                static::assertSame($expectedTaxValue, $lineItem->getTax()->getValue());
            }
        }

        foreach ($result as $lineItem) {
            static::assertInstanceOf(Item::class, $lineItem);
            static::assertSame($expectedUnitAmountValue, $lineItem->getUnitAmount()->getValue());
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getItemListTestDataProvider()
    {
        yield 'Is PayUponInvoice and use gross prices' => [
            [
                'sCurrencyName' => 'EUR',
                'content' => [
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'foo', 'ordernumber' => 'foo', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'bar', 'ordernumber' => 'bar', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'fooBar', 'ordernumber' => 'fooBar', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                ],
            ],
            ['additional' => ['show_net' => true, 'charge_vat' => true]],
            PaymentType::PAYPAL_PAY_UPON_INVOICE_V2,
            true,
            '15.97',
            '84.03',
        ];

        yield 'Is PayUponInvoice and use net prices' => [
            [
                'sCurrencyName' => 'EUR',
                'content' => [
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'foo', 'ordernumber' => 'foo', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'bar', 'ordernumber' => 'bar', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'fooBar', 'ordernumber' => 'fooBar', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                ],
            ],
            ['additional' => ['show_net' => false, 'charge_vat' => true]],
            PaymentType::PAYPAL_PAY_UPON_INVOICE_V2,
            true,
            '15.97',
            '84.03',
        ];

        yield 'Is any paymentMethod and use gross prices' => [
            [
                'sCurrencyName' => 'EUR',
                'content' => [
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'foo', 'ordernumber' => 'foo', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'bar', 'ordernumber' => 'bar', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'fooBar', 'ordernumber' => 'fooBar', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                ],
            ],
            ['additional' => ['show_net' => true, 'charge_vat' => true]],
            'paymentMethod',
            false,
            '0.00',
            '100',
        ];

        yield 'Is any paymentMethod and use net prices' => [
            [
                'sCurrencyName' => 'EUR',
                'content' => [
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'foo', 'ordernumber' => 'foo', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'bar', 'ordernumber' => 'bar', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'fooBar', 'ordernumber' => 'fooBar', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                ],
            ],
            ['additional' => ['show_net' => false, 'charge_vat' => true]],
            'paymentMethod',
            true,
            '15.97',
            '84.03',
        ];

        yield 'Is any paymentMethod use net prices and dont change vat' => [
            [
                'sCurrencyName' => 'EUR',
                'content' => [
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'foo', 'ordernumber' => 'foo', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'bar', 'ordernumber' => 'bar', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                    ['tax_rate' => '19.00', 'tax' => '15.97', 'esdarticle' => 0, 'articlename' => 'fooBar', 'ordernumber' => 'fooBar', 'quantity' => 1, 'priceNumeric' => '100.00', 'netprice' => '84.03', 'customProductMode' => false],
                ],
            ],
            ['additional' => ['show_net' => false, 'charge_vat' => false]],
            'paymentMethod',
            true,
            '0.00',
            '84.03',
        ];
    }

    /**
     * @return ItemListProvider
     */
    private function createItemListProvider()
    {
        $snippetNameSpace = $this->createMock(Enlight_Components_Snippet_Namespace::class);

        $snippetManager = $this->createMock(Shopware_Components_Snippet_Manager::class);
        $snippetManager->method('getNamespace')->willReturn($snippetNameSpace);

        return new ItemListProvider(
            $this->createMock(LoggerService::class),
            $snippetManager,
            new PriceFormatter(),
            new CustomerHelper()
        );
    }
}
