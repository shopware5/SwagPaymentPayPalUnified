<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Components\Patches;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ItemList\Item;

class PaymentItemsPatch implements PatchInterface
{
    const PATH = '/transactions/0/item_list/items';

    /**
     * @var Item[]
     */
    private $items;

    /**
     * @param array $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation()
    {
        return self::OPERATION_REPLACE;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return self::PATH;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $items = [];
        foreach ($this->items as $item) {
            $items[] = $item->toArray();
        }

        return $items;
    }
}
