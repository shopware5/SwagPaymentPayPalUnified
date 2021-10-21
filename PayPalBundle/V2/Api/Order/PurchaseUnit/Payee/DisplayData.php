<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payee;

use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class DisplayData extends PayPalApiStruct
{
    /**
     * @var string
     */
    protected $brandName;

    /**
     * @return string
     */
    public function getBrandName()
    {
        return $this->brandName;
    }

    /**
     * @param string $brandName
     *
     * @return void
     */
    public function setBrandName($brandName)
    {
        $this->brandName = $brandName;
    }
}
