<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Services\PayPalOrder;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\Common\CartHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\AmountProvider;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\ItemTotal;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\TaxTotal;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item;

class AmountProviderTest extends TestCase
{
    /**
     * @var MockObject|CartHelper
     */
    private $cartHelper;

    /**
     * @var MockObject|CustomerHelper
     */
    private $customerHelper;

    /**
     * @var MockObject|PriceFormatter
     */
    private $priceFormatter;

    /**
     * @var MockObject|PurchaseUnit
     */
    private $purchaseUnit;

    /**
     * @before
     *
     * @return void
     */
    public function init()
    {
        $this->cartHelper = static::createMock(CartHelper::class);
        $this->customerHelper = static::createMock(CustomerHelper::class);
        $this->priceFormatter = static::createMock(PriceFormatter::class);

        $this->purchaseUnit = static::createMock(PurchaseUnit::class);
    }

    /**
     * @return void
     */
    public function testThereIsNoBreakdownWhenThereAreNoItems()
    {
        $amount = $this->getAmountProvider()->createAmount([], $this->purchaseUnit, []);

        static::assertNull($amount->getBreakdown());
    }

    /**
     * @dataProvider impreciseDataProvider
     * @dataProvider preciseDataProvider
     *
     * @param Item[] $items
     *
     * @return void
     */
    public function testBreakdownIsValid($items)
    {
        $this->givenTheItems($items);

        $amount = $this->getAmountProvider(
            new CartHelper(new CustomerHelper(), new PriceFormatter()),
            new CustomerHelper(),
            new PriceFormatter()
        )->createAmount(
            Fixture::CART,
            $this->purchaseUnit,
            Fixture::CUSTOMER
        );

        static::assertInstanceOf(Breakdown::class, $amount->getBreakdown());
        static::assertInstanceOf(TaxTotal::class, $amount->getBreakdown()->getTaxTotal());
        static::assertInstanceOf(ItemTotal::class, $amount->getBreakdown()->getItemTotal());

        $total = (float) $amount->getValue();

        $taxTotal = (float) $amount->getBreakdown()->getTaxTotal()->getValue();
        $itemTotal = (float) $amount->getBreakdown()->getItemTotal()->getValue();

        $cartTaxTotal = (float) Fixture::CART['sAmountTax'];
        $cartItemTotal = (float) Fixture::CART['AmountNetNumeric'];

        // Check whether the breakdown is consistent with the amount
        static::assertSame(
            $total,
            $taxTotal + $itemTotal,
            sprintf('The breakdown is inconsistent (itemTotal + taxTotal != total). %.2f + %.2f = %.2f != %.2f', $itemTotal, $taxTotal, $taxTotal + $itemTotal, $total)
        );
    }

    /**
     * @return array<array{0: Item[]}>
     */
    public function preciseDataProvider()
    {
        return [
            [
                [Fixture::getItem()],
            ],
        ];
    }

    /**
     * @return array<array{0: Item[]}>
     */
    public function impreciseDataProvider()
    {
        return [
            [
                [Fixture::getItemWithRoundedAmounts()],
            ],
        ];
    }

    /**
     * @return AmountProvider
     */
    protected function getAmountProvider(
        CartHelper $cartHelper = null,
        CustomerHelper $customerHelper = null,
        PriceFormatter $priceFormatter = null
    ) {
        return new AmountProvider(
            $cartHelper ?: $this->cartHelper,
            $customerHelper ?: $this->customerHelper,
            $priceFormatter ?: $this->priceFormatter
        );
    }

    /**
     * @param Item[] $items
     *
     * @return void
     */
    private function givenTheItems($items)
    {
        $this->purchaseUnit->method('getItems')
            ->willReturn($items);
    }
}
