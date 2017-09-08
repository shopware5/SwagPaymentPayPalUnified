<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
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
