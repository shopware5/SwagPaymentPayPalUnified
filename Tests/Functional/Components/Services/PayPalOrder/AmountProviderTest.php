<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\PayPalOrder;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\AmountProvider;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\Discount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\Tax;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\UnitAmount;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;

class AmountProviderTest extends TestCase
{
    use ContainerTrait;

    const CURRENCY_CODE = 'EUR';

    /**
     * @return void
     */
    public function testCreateAmountWithADiscount()
    {
        $amountProvider = $this->createAmountProvider();

        $result = $amountProvider->createAmount($this->createCart(), $this->createPurchaseUnit(), $this->createCustomer());

        $breakDown = $result->getBreakdown();
        static::assertInstanceOf(Breakdown::class, $breakDown);

        $discount = $breakDown->getDiscount();
        static::assertInstanceOf(Discount::class, $discount);

        static::assertSame('10.00', $discount->getValue());
    }

    /**
     * @return void
     */
    public function testCreateAmountWithoutTaxWithADiscount()
    {
        $amountProvider = $this->createAmountProvider();

        $purchaseUnit = new PurchaseUnit();
        $item = $this->createItem('33.57', '6.38', 'SW10170');
        $item2 = $this->createItem('99.00', '0.00', 'SW10171', false, false);
        $discount = $this->createItem('-10.00', '-1.60', 'PROMOTION', true, false);

        $purchaseUnit->setItems([
            $item,
            $item2,
            $discount,
        ]);

        $result = $amountProvider->createAmount($this->createCart(), $purchaseUnit, $this->createCustomer());

        $breakDown = $result->getBreakdown();
        static::assertInstanceOf(Breakdown::class, $breakDown);

        $discount = $breakDown->getDiscount();
        static::assertInstanceOf(Discount::class, $discount);

        static::assertSame('10.00', $discount->getValue());
    }

    /**
     * @return PurchaseUnit
     */
    private function createPurchaseUnit()
    {
        $purchaseUnit = new PurchaseUnit();
        $item = $this->createItem('33.57', '6.38', 'SW10170');
        $discount = $this->createItem('-8.4', '-1.60', 'PROMOTION', true);

        $purchaseUnit->setItems([
            $item,
            $discount,
        ]);

        return $purchaseUnit;
    }

    /**
     * @param string $value
     * @param string $taxValue
     * @param string $ordernumber
     * @param bool   $isDiscount
     * @param bool   $hasTax
     *
     * @return Item
     */
    private function createItem($value, $taxValue, $ordernumber, $isDiscount = false, $hasTax = true)
    {
        $item = new Item();
        $item->setName($isDiscount ? 'DISCOUNT' : 'Some Product');
        $item->setTaxRate('19.0');
        $item->setQuantity(1);
        $item->setSku($ordernumber);
        $item->setCategory('PHYSICAL_GOODS');

        $itemUnitAmount = new UnitAmount();
        $itemUnitAmount->setValue($value);
        $itemUnitAmount->setCurrencyCode(self::CURRENCY_CODE);

        $item->setUnitAmount($itemUnitAmount);

        if ($hasTax) {
            $itemTax = new Tax();
            $itemTax->setCurrencyCode(self::CURRENCY_CODE);
            $itemTax->setValue($taxValue);

            $item->setTax($itemTax);
        }

        return $item;
    }

    /**
     * @return array<string,mixed>
     */
    private function createCustomer()
    {
        return [
            'additional' => [
                'show_net' => true,
                'countryShipping' => [
                    'taxfree' => 0,
                    'taxfree_ustid' => 0,
                ],
                'country' => [
                    'taxfree_ustid' => 0,
                ],
            ],
            'shippingaddress' => [
                'ustid' => null,
            ],
        ];
    }

    /**
     * @return array<string|float>
     */
    private function createCart()
    {
        return [
            'sCurrencyName' => self::CURRENCY_CODE,
            'sAmountTax' => 10.529999999999999,
            'sShippingcostsWithTax' => 35.990000000000002,
            'sShippingcostsNet' => 30.239999999999998,
        ];
    }

    /**
     * @return AmountProvider
     */
    private function createAmountProvider()
    {
        return new AmountProvider(
            $this->getContainer()->get('paypal_unified.common.cart_helper'),
            $this->getContainer()->get('paypal_unified.common.customer_helper'),
            $this->getContainer()->get('paypal_unified.common.price_formatter')
        );
    }
}
