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
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\Tax;

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

        $amount->setBreakdown(
            $this->createBreakdown(
                $purchaseUnit,
                $currencyCode,
                $cart,
                $customer
            )
        );

        return $amount;
    }

    /**
     * @param string $currencyCode
     *
     * @return Breakdown|null
     */
    private function createBreakdown(
        PurchaseUnit $purchaseUnit,
        $currencyCode,
        array $cart,
        array $customer
    ) {
        $itemTotalValue = 0.0;
        $discountValue = 0.0;
        $newItems = [];

        // Only set breakdown if items are submitted, otherwise the breakdown will be invalid
        if ($purchaseUnit->getItems() === null) {
            return null;
        }

        foreach ($purchaseUnit->getItems() as $item) {
            $itemUnitAmount = (float) $item->getUnitAmount()->getValue();
            if ($itemUnitAmount < 0.0) {
                if (!$item->getTax() instanceof Tax) {
                    $discountValue += $item->getQuantity() * abs($itemUnitAmount);
                } else {
                    $discountValue += $item->getQuantity() * (abs($itemUnitAmount) + abs($item->getTax()->getValue()));
                }
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
        $provideTaxTotal = !$this->customerHelper->usesGrossPrice($customer) && !$this->customerHelper->hasNetPriceCaluclationIndicator($customer);

        if ($this->isTaxDeclaredOnItems($purchaseUnit->getItems())) {
            /**
             * We need to provide a total tax value which will pass the checks
             * done by the PayPal-API. The logic it uses is described in the
             * docs (`tax * quantity` for each item).
             *
             * @see https://developer.paypal.com/api/rest/reference/orders/v2/errors#unprocessable-entity (TAX_TOTAL_MISMATCH)
             */
            $taxTotalValue = array_reduce($newItems, static function ($total, Item $item) {
                if (!$item->getTax() instanceof Tax) {
                    return $total;
                }

                return $total + (float) $item->getTax()->getValue() * $item->getQuantity();
            }, 0.0);

            // When tax is declared for items, providing the total is mandatory.
            $provideTaxTotal = true;
        } else {
            $taxTotalValue = $cart['sAmountTax'];
        }

        if ($this->customerHelper->usesGrossPrice($customer) && !$this->customerHelper->hasNetPriceCaluclationIndicator($customer)) {
            $shipping->setValue($this->priceFormatter->formatPrice($cart['sShippingcostsWithTax']));
        } elseif (!$this->customerHelper->usesGrossPrice($customer) && !$this->customerHelper->hasNetPriceCaluclationIndicator($customer)) {
            //Case 2: Show net prices in shopware and don't exclude country tax
            $shipping->setValue($this->priceFormatter->formatPrice($cart['sShippingcostsNet']));
        } else {
            //Case 3: No tax handling at all, just use the net amounts.
            $shipping->setValue($this->priceFormatter->formatPrice($cart['sShippingcostsNet']));
        }

        if ($provideTaxTotal) {
            $taxTotal = new TaxTotal();
            $taxTotal->setCurrencyCode($currencyCode);
            $taxTotal->setValue($this->priceFormatter->formatPrice($taxTotalValue));
        }

        $breakdown = new Breakdown();
        $breakdown->setItemTotal($itemTotal);
        $breakdown->setShipping($shipping);
        $breakdown->setTaxTotal($taxTotal);

        if ($discountValue > 0.0) {
            $discount = new Discount();
            $discount->setCurrencyCode($currencyCode);
            $discount->setValue($this->priceFormatter->formatPrice($discountValue));
            $breakdown->setDiscount($discount);
        }

        return $breakdown;
    }

    /**
     * @param Item[]|null $items
     *
     * @return bool
     */
    private function isTaxDeclaredOnItems($items)
    {
        if (empty($items)) {
            return false;
        }

        foreach ($items as $item) {
            if ($item->getTax() !== null && !empty($item->getTaxRate())) {
                return true;
            }
        }

        return false;
    }
}
