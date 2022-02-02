<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization\SellerProtection;

class Authorization extends Payment
{
    /**
     * @var SellerProtection
     */
    protected $sellerProtection;

    /**
     * @var string
     */
    protected $expirationTime;

    /**
     * @return SellerProtection
     */
    public function getSellerProtection()
    {
        return $this->sellerProtection;
    }

    /**
     * @return void
     */
    public function setSellerProtection(SellerProtection $sellerProtection)
    {
        $this->sellerProtection = $sellerProtection;
    }

    /**
     * @return string
     */
    public function getExpirationTime()
    {
        return $this->expirationTime;
    }

    /**
     * @param string $expirationTime
     *
     * @return void
     */
    public function setExpirationTime($expirationTime)
    {
        $this->expirationTime = $expirationTime;
    }
}
