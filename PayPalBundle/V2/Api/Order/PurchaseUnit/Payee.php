<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payee\DisplayData;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Payee extends PayPalApiStruct
{
    /**
     * @var string
     */
    protected $emailAddress;

    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @var DisplayData
     */
    protected $displayData;

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     *
     * @return void
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     *
     * @return void
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return \SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payee\DisplayData
     */
    public function getDisplayData()
    {
        return $this->displayData;
    }

    /**
     * @return void
     */
    public function setDisplayData(DisplayData $displayData)
    {
        $this->displayData = $displayData;
    }
}
