<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping;

use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Name extends PayPalApiStruct
{
    /**
     * @var string
     */
    protected $fullName;

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     *
     * @return void
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }
}
