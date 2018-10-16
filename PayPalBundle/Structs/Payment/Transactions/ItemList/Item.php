<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ItemList;

class Item
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var string
     */
    private $price;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $quantity;

    /**
     * @var string
     */
    private $tax;

    /**
     * @return string
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param string $tax
     */
    public function setTax($tax)
    {
        $this->tax = (string) $tax;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = (string) $price;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = (string) $quantity;
    }

    /**
     * @param array $data
     *
     * @return Item
     */
    public static function fromArray(array $data)
    {
        $result = new self();

        $result->setName($data['name']);
        $result->setSku($data['sku']);
        $result->setPrice((float) $data['price']);
        $result->setCurrency($data['currency']);
        $result->setTax($data['tax']);
        $result->setQuantity((int) $data['quantity']);

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        //We don't work with taxes in this case to avoid calculation errors.
        return [
            'name' => $this->getName(),
            'sku' => $this->getSku(),
            'price' => $this->getPrice(),
            'currency' => $this->getCurrency(),
            'quantity' => $this->getQuantity(),
        ];
    }
}
