<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Services\OrderBuilder\OrderHandler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\AmountProvider;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\ItemListProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\Discount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\Handling;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\ItemTotal;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\TaxTotal;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item;
use SwagPaymentPayPalUnified\Tests\Unit\Components\Services\PayPalOrder\Fixture;

class AbstractOrderHandlerTest extends TestCase
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var ItemListProvider&MockObject
     */
    private $itemListProvider;

    /**
     * @var AmountProvider&MockObject
     */
    private $amountProvider;

    /**
     * @var ReturnUrlHelper
     */
    private $returnUrlHelper;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var PayPalOrderParameter&MockObject
     */
    private $paypalOrderParameter;

    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @before
     *
     * @return void
     */
    public function init()
    {
        $this->settingsService = $this->createMock(SettingsServiceInterface::class);
        $this->itemListProvider = $this->createMock(ItemListProvider::class);
        $this->amountProvider = $this->createMock(AmountProvider::class);
        $this->returnUrlHelper = $this->createMock(ReturnUrlHelper::class);
        $this->contextService = $this->createMock(ContextServiceInterface::class);
        $this->priceFormatter = $this->createMock(PriceFormatter::class);
        $this->customerHelper = $this->createMock(CustomerHelper::class);

        $this->paypalOrderParameter = $this->createMock(PayPalOrderParameter::class);
    }

    /**
     * Asserts that the AbstractOrderHandler returns a purchaseUnit with a
     * correctly calculated breakdown, even when the individual item amounts
     * deviate from their actual value, due to rounding errors.
     *
     * @dataProvider preciseDataProvider
     * @dataProvider impreciseDataProvider
     *
     * @param Item[] $items
     *
     * @return void
     */
    public function testItPassesPayPalCalculation(array $items, Amount $amount)
    {
        $this->givenTheCart(Fixture::CART);
        $this->givenTheCustomer(Fixture::CUSTOMER);
        $this->givenThePaymentType(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2);

        $this->givenTheItems($items);
        $this->givenTheAmount($amount);

        $this->givenThePriceFormatter(new PriceFormatter());

        $purchaseUnit = $this->getFirstPurchaseUnit($this->getAbstractOrderHandler()->createPurchaseUnits($this->paypalOrderParameter));
        $item = $purchaseUnit->getItems() ? $purchaseUnit->getItems()[0] : null;

        static::assertInstanceOf(Item::class, $item);

        $amount = $purchaseUnit->getAmount();

        static::assertInstanceOf(Amount::class, $amount);

        $breakdown = $amount->getBreakdown();

        static::assertInstanceOf(Breakdown::class, $breakdown);
        static::assertInstanceOf(ItemTotal::class, $breakdown->getItemTotal());
        static::assertInstanceOf(TaxTotal::class, $breakdown->getTaxTotal());

        if (\strpos($this->getDataSetAsString(), 'Precise') === false) {
            /*
             * This case currently only makes sense, when we're calculating the
             * breakdown using rounded values, like PayPal does. If the API
             * supports more than 2 decimal places for item unit amounts in the
             * future, the condition around this assertion should be removed.
             */
            static::assertSame(2, \strlen(explode('.', $item->getUnitAmount()->getValue())[1]));
            static::assertEquals(
                (float) $breakdown->getItemTotal()->getValue(),
                $this->paypalItemAmountCalculation($purchaseUnit)
            );
        }

        $finalAmount = (float) $breakdown->getItemTotal()->getValue() + (float) $breakdown->getTaxTotal()->getValue();

        if ($breakdown->getDiscount() instanceof Discount) {
            $finalAmount -= (float) $breakdown->getDiscount()->getValue();
        }

        if ($breakdown->getHandling() instanceof Handling) {
            $finalAmount += (float) $breakdown->getHandling()->getValue();
        }

        static::assertEquals(
            $finalAmount,
            $amount->getValue()
        );
    }

    /**
     * @return array<array{0: Item[], 1: Amount}>
     */
    public function preciseDataProvider()
    {
        return [
            'Precise' => [
                [Fixture::getItem()],
                Fixture::getAmount(),
            ],
        ];
    }

    /**
     * @return array<array{0: Item[], 1: Amount}>
     */
    public function impreciseDataProvider()
    {
        return [
            'Imprecise' => [
                [Fixture::getItemWithRoundedAmounts()],
                Fixture::getMiscalculatedAmount(),
            ],
        ];
    }

    /**
     * @param SettingsServiceInterface|null $settingsService
     * @param ItemListProvider|null         $itemListProvider
     * @param AmountProvider|null           $amountProvider
     * @param ReturnUrlHelper|null          $returnUrlHelper
     * @param ContextServiceInterface|null  $contextService
     * @param PriceFormatter|null           $priceFormatter
     * @param CustomerHelper|null           $customerHelper
     *
     * @return TestOrderHandler
     */
    protected function getAbstractOrderHandler(
        $settingsService = null,
        $itemListProvider = null,
        $amountProvider = null,
        $returnUrlHelper = null,
        $contextService = null,
        $priceFormatter = null,
        $customerHelper = null
    ) {
        return new TestOrderHandler(
            $settingsService ?: $this->settingsService,
            $itemListProvider ?: $this->itemListProvider,
            $amountProvider ?: $this->amountProvider,
            $returnUrlHelper ?: $this->returnUrlHelper,
            $contextService ?: $this->contextService,
            $priceFormatter ?: $this->priceFormatter,
            $customerHelper ?: $this->customerHelper
        );
    }

    /**
     * @param array<string, mixed> $cart
     *
     * @return void
     */
    protected function givenTheCart($cart)
    {
        $this->paypalOrderParameter->method('getCart')
            ->willReturn($cart);
    }

    /**
     * @param array<string, mixed> $customer
     *
     * @return void
     */
    protected function givenTheCustomer($customer)
    {
        $this->paypalOrderParameter->method('getCustomer')
            ->willReturn($customer);
    }

    /**
     * @param PaymentType::* $paymentType
     *
     * @return void
     */
    protected function givenThePaymentType($paymentType)
    {
        $this->paypalOrderParameter->method('getPaymentType')
            ->willReturn($paymentType);
    }

    /**
     * @param Item[] $items
     *
     * @return void
     */
    protected function givenTheItems($items)
    {
        $this->itemListProvider->method('getItemList')
            ->willReturn($items);
    }

    /**
     * @param PurchaseUnit[]|null $purchaseUnits
     *
     * @return PurchaseUnit
     */
    protected function getFirstPurchaseUnit($purchaseUnits)
    {
        if (\method_exists(self::class, 'assertIsArray')) {
            static::assertIsArray($purchaseUnits);
        } else {
            static::assertTrue(\is_array($purchaseUnits));
        }

        static::assertNotEmpty($purchaseUnits);

        $item = array_pop($purchaseUnits);

        static::assertInstanceOf(PurchaseUnit::class, $item);

        return $item;
    }

    /**
     * @param Amount $amount
     *
     * @return void
     */
    private function givenTheAmount($amount)
    {
        $this->amountProvider->method('createAmount')
            ->willReturn($amount);
    }

    /**
     * @return float
     */
    private function paypalItemAmountCalculation(PurchaseUnit $purchaseUnit)
    {
        $items = $purchaseUnit->getItems();

        if ($items === null) {
            return 0.0;
        }

        return array_reduce($items, static function ($carry, $item) {
            return $carry + (float) $item->getUnitAmount()->getValue() * $item->getQuantity();
        }, 0.0);
    }

    /**
     * @param PriceFormatter $priceFormatter
     *
     * @return void
     */
    private function givenThePriceFormatter($priceFormatter)
    {
        $this->priceFormatter = $priceFormatter;
    }
}
