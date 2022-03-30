<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\Discount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\Handling;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\Insurance;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\ItemTotal;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\Shipping;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\ShippingDiscount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\TaxTotal;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Breakdown extends PayPalApiStruct
{
    /**
     * @var ItemTotal
     */
    protected $itemTotal;

    /**
     * @var Shipping
     */
    protected $shipping;

    /**
     * @var Handling
     */
    protected $handling;

    /**
     * @var TaxTotal|null
     */
    protected $taxTotal;

    /**
     * @var Insurance
     */
    protected $insurance;

    /**
     * @var ShippingDiscount
     */
    protected $shippingDiscount;

    /**
     * @var Discount
     */
    protected $discount;

    /**
     * @return ItemTotal|null
     */
    public function getItemTotal()
    {
        return $this->itemTotal;
    }

    /**
     * @return void
     */
    public function setItemTotal(ItemTotal $itemTotal)
    {
        $this->itemTotal = $itemTotal;
    }

    /**
     * @return Shipping|null
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @return void
     */
    public function setShipping(Shipping $shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return Handling|null
     */
    public function getHandling()
    {
        return $this->handling;
    }

    /**
     * @return void
     */
    public function setHandling(Handling $handling)
    {
        $this->handling = $handling;
    }

    /**
     * @return TaxTotal|null
     */
    public function getTaxTotal()
    {
        return $this->taxTotal;
    }

    /**
     * @param TaxTotal|null $taxTotal
     *
     * @return void
     */
    public function setTaxTotal($taxTotal)
    {
        $this->taxTotal = $taxTotal;
    }

    /**
     * @return Insurance|null
     */
    public function getInsurance()
    {
        return $this->insurance;
    }

    /**
     * @return void
     */
    public function setInsurance(Insurance $insurance)
    {
        $this->insurance = $insurance;
    }

    /**
     * @return ShippingDiscount|null
     */
    public function getShippingDiscount()
    {
        return $this->shippingDiscount;
    }

    /**
     * @return void
     */
    public function setShippingDiscount(ShippingDiscount $shippingDiscount)
    {
        $this->shippingDiscount = $shippingDiscount;
    }

    /**
     * @return Discount|null
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @return void
     */
    public function setDiscount(Discount $discount)
    {
        $this->discount = $discount;
    }
}
