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
    protected $payerId;

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
    public function getPayerId()
    {
        return $this->payerId;
    }

    /**
     * @param string $payerId
     *
     * @return void
     */
    public function setPayerId($payerId)
    {
        $this->payerId = $payerId;
    }

    /**
     * @return DisplayData
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
