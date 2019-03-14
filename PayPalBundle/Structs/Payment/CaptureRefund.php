<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\Amount;

class CaptureRefund
{
    /**
     * @var Amount|null
     */
    private $amount;

    /**
     * @var string
     */
    private $description;

    /**
     * @return Amount|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param Amount $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        //If the amount object is null, we do not need to add it to the array.
        //Note: A sale/capture will be refunded completely in that case
        return $this->getAmount() === null
            ? ['description' => $this->getDescription()]
            : ['description' => $this->getDescription(), 'amount' => $this->getAmount()->toArray()];
    }
}
