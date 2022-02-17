<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Common;

class CartHelper
{
    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    public function __construct(CustomerHelper $customerHelper, PriceFormatter $priceFormatter)
    {
        $this->customerHelper = $customerHelper;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * @return string
     */
    public function getTotalAmount(array $cart, array $customer)
    {
        //Case 1: Show gross prices in shopware and don't exclude country tax
        if ($this->customerHelper->usesGrossPrice($customer) && !$this->customerHelper->hasNetPriceCaluclationIndicator($customer)) {
            return $this->priceFormatter->formatPrice($cart['AmountNumeric']);
        }

        //Case 2: Show net prices in shopware and don't exclude country tax
        if (!$this->customerHelper->usesGrossPrice($customer) && !$this->customerHelper->hasNetPriceCaluclationIndicator($customer)) {
            return $this->priceFormatter->formatPrice($cart['AmountWithTaxNumeric']);
        }

        //Case 3: No tax handling at all, just use the net amounts.
        return $this->priceFormatter->formatPrice($cart['AmountNetNumeric']);
    }
}
