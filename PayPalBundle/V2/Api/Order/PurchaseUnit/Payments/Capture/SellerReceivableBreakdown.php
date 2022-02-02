<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture\SellerReceivableBreakdown\GrossAmount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture\SellerReceivableBreakdown\NetAmount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture\SellerReceivableBreakdown\PaypalFee;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class SellerReceivableBreakdown extends PayPalApiStruct
{
    /**
     * @var GrossAmount
     */
    protected $grossAmount;

    /**
     * @var PaypalFee
     */
    protected $paypalFee;

    /**
     * @var NetAmount
     */
    protected $netAmount;

    /**
     * @return GrossAmount
     */
    public function getGrossAmount()
    {
        return $this->grossAmount;
    }

    /**
     * @return void
     */
    public function setGrossAmount(GrossAmount $grossAmount)
    {
        $this->grossAmount = $grossAmount;
    }

    /**
     * @return PaypalFee
     */
    public function getPaypalFee()
    {
        return $this->paypalFee;
    }

    /**
     * @return void
     */
    public function setPaypalFee(PaypalFee $paypalFee)
    {
        $this->paypalFee = $paypalFee;
    }

    /**
     * @return NetAmount
     */
    public function getNetAmount()
    {
        return $this->netAmount;
    }

    /**
     * @return void
     */
    public function setNetAmount(NetAmount $netAmount)
    {
        $this->netAmount = $netAmount;
    }
}
