<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\PayPalOrder;

use SwagPaymentPayPalUnified\Components\Services\Common\CartHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\Discount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\ItemTotal;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\Shipping as BreakdownShipping;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\TaxTotal;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item;

class AmountProvider
{
    /**
     * @var CartHelper
     */
    private $cartHelper;

    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    public function __construct(CartHelper $cartHelper, CustomerHelper $customerHelper, PriceFormatter $priceFormatter)
    {
        $this->cartHelper = $cartHelper;
        $this->customerHelper = $customerHelper;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * @return Amount
     */
    public function createAmount(array $cart, PurchaseUnit $purchaseUnit, array $customer)
    {
        $currencyCode = $cart['sCurrencyName'];

        $amount = new Amount();
        $amount->setCurrencyCode($currencyCode);
        $amount->setValue($this->cartHelper->getTotalAmount($cart, $customer));

        $items = $purchaseUnit->getItems();
        if ($items === null) {
            return $amount;
        }

        // Only set breakdown if items are submitted, otherwise the breakdown will be invalid
        $amount->setBreakdown(
            $this->createBreakdown(
                $items,
                $purchaseUnit,
                $currencyCode,
                $cart,
                $customer
            )
        );

        return $amount;
    }

    /**
     * @param Item[] $items
     * @param string $currencyCode
     *
     * @return Breakdown
     */
    private function createBreakdown(
        array $items,
        PurchaseUnit $purchaseUnit,
        $currencyCode,
        array $cart,
        array $customer
    ) {
        $itemTotalValue = 0.0;
        $discountValue = 0.0;
        $newItems = [];
        foreach ($items as $item) {
            $itemUnitAmount = (float) $item->getUnitAmount()->getValue();
            if ($itemUnitAmount < 0.0) {
                $discountValue += ($itemUnitAmount * -1);
            } else {
                $itemTotalValue += $item->getQuantity() * $itemUnitAmount;
                $newItems[] = $item;
            }
        }
        $purchaseUnit->setItems($newItems);

        $itemTotal = new ItemTotal();
        $itemTotal->setCurrencyCode($currencyCode);
        $itemTotal->setValue($this->priceFormatter->formatPrice($itemTotalValue));

        $shipping = new BreakdownShipping();
        $shipping->setCurrencyCode($currencyCode);
        $taxTotal = null;

        if ($this->customerHelper->hasGrossPrices($customer) && !$this->customerHelper->useNetPriceCalculation($customer)) {
            $shipping->setValue($cart['sShippingcostsWithTax']);
        } elseif (!$this->customerHelper->hasGrossPrices($customer) && !$this->customerHelper->useNetPriceCalculation($customer)) {
            //Case 2: Show net prices in shopware and don't exclude country tax
            $shipping->setValue($cart['sShippingcostsNet']);

            $taxTotal = new TaxTotal();
            $taxTotal->setCurrencyCode($currencyCode);
            $taxTotal->setValue($this->priceFormatter->formatPrice($cart['sAmountTax']));
        } else {
            //Case 3: No tax handling at all, just use the net amounts.
            $shipping->setValue($cart['sShippingcostsNet']);
        }

        $discount = new Discount();
        $discount->setCurrencyCode($currencyCode);
        $discount->setValue($this->priceFormatter->formatPrice($discountValue));

        $breakdown = new Breakdown();
        $breakdown->setItemTotal($itemTotal);
        $breakdown->setShipping($shipping);
        $breakdown->setTaxTotal($taxTotal);
        $breakdown->setDiscount($discount);

        return $breakdown;
    }
}
