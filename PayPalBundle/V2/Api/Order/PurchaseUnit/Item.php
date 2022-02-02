<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\Tax;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\UnitAmount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Item extends PayPalApiStruct
{
    const MAX_LENGTH_NAME = 127;
    const MAX_LENGTH_SKU = 127;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var UnitAmount
     */
    protected $unitAmount;

    /**
     * @var Tax
     */
    protected $tax;

    /**
     * @var string|null
     */
    protected $taxRate;

    /**
     * @var int
     */
    protected $quantity;

    /**
     * @var string|null
     */
    protected $sku;

    /**
     * @var string|null
     *
     * @see https://developer.paypal.com/docs/api/orders/v2/#definition-item
     */
    protected $category;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @throws \LengthException if given parameter is too long
     *
     * @return void
     */
    public function setName($name)
    {
        if (\mb_strlen($name) > self::MAX_LENGTH_NAME) {
            throw new \LengthException(
                \sprintf('%s::$name must not be longer than %s characters', self::class, self::MAX_LENGTH_NAME)
            );
        }

        $this->name = $name;
    }

    /**
     * @return UnitAmount
     */
    public function getUnitAmount()
    {
        return $this->unitAmount;
    }

    /**
     * @return void
     */
    public function setUnitAmount(UnitAmount $unitAmount)
    {
        $this->unitAmount = $unitAmount;
    }

    /**
     * @return Tax
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @return void
     */
    public function setTax(Tax $tax)
    {
        $this->tax = $tax;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int|string $quantity
     *
     * @return void
     */
    public function setQuantity($quantity)
    {
        $this->quantity = (int) $quantity;
    }

    /**
     * @return string|null
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string|null $sku
     *
     * @throws \LengthException if given parameter is too long
     *
     * @return void
     */
    public function setSku($sku)
    {
        if ($sku !== null && \mb_strlen($sku) > self::MAX_LENGTH_SKU) {
            throw new \LengthException(
                \sprintf('%s::$sku must not be longer than %s characters', self::class, self::MAX_LENGTH_SKU)
            );
        }

        $this->sku = $sku;
    }

    /**
     * @return string|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return string|null
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @param string|null $taxRate
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
    }
}
