<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources;

class Order extends RelatedResource
{
    /**
     * @param array $data
     *
     * @return Order
     */
    public static function fromArray(array $data)
    {
        $result = new self();
        $result->prepare($result, $data, ResourceType::ORDER);

        return $result;
    }
}
