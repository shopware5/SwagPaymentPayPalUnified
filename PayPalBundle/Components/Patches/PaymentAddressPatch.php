<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Components\Patches;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ItemList\ShippingAddress;

class PaymentAddressPatch implements PatchInterface
{
    const PATH = '/transactions/0/item_list/shipping_address';

    /**
     * @var ShippingAddress
     */
    private $address;

    /**
     * @param ShippingAddress $address
     */
    public function __construct(ShippingAddress $address)
    {
        $this->address = $address;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation()
    {
        return self::OPERATION_ADD;
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
        return $this->address->toArray();
    }
}
