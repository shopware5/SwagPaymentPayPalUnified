<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;

use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class SellerProtection extends PayPalApiStruct
{
    /**
     * @var string
     */
    protected $status;

    /**
     * @var string[]
     */
    protected $disputeCategories;

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return void
     */
    public function setStatus($status)
    {
        $status = (string) $status;
        $this->status = $status;
    }

    /**
     * @return string[]
     */
    public function getDisputeCategories()
    {
        return $this->disputeCategories;
    }

    /**
     * @param string[] $disputeCategories
     *
     * @return void
     */
    public function setDisputeCategories(array $disputeCategories)
    {
        $this->disputeCategories = $disputeCategories;
    }
}
