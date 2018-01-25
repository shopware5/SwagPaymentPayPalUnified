<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ItemList\Item;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ItemList\ShippingAddress;

class ItemList
{
    /**
     * @var Item[]
     */
    private $items;

    /**
     * @var ShippingAddress
     */
    private $shippingAddress;

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item[] $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return ShippingAddress
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param ShippingAddress $shippingAddress
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @param array $data
     *
     * @return ItemList
     */
    public static function fromArray(array $data)
    {
        $result = new self();

        $items = [];

        foreach ($data['items'] as $item) {
            $items[] = Item::fromArray($item);
        }

        $result->setItems($items);
        $result->setShippingAddress(ShippingAddress::fromArray($data['shipping_address']));

        return $result;
    }

    /**
     * @return array|null
     */
    public function toArray()
    {
        $result = null;

        /** @var Item $item */
        foreach ($this->getItems() as $item) {
            $result['items'][] = $item->toArray();
        }

        if ($this->getShippingAddress() !== null) {
            $result['shipping_address'] = $this->getShippingAddress()->toArray();
        }

        return $result;
    }
}
