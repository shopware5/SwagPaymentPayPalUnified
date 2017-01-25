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

namespace SwagPaymentPayPalUnified\SDK\Structs\Payment\Transactions;

use SwagPaymentPayPalUnified\SDK\Structs\Payment\Transactions\ItemList\Item;

class ItemList
{
    /**
     * @var Item[] $items
     */
    private $items;

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
     * @param array $data
     * @return ItemList
     */
    public static function fromArray(array $data)
    {
        $result = new ItemList();

        $items = [];

        foreach ($data['items'] as $item) {
            $items[] = Item::fromArray($item);
        }

        $result->setItems($items);

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

        return $result;
    }
}
