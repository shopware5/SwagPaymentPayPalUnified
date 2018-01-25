<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\Amount;

class Capture
{
    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var bool
     */
    private $isFinalCapture;

    /**
     * @return Amount
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
     * @return bool
     */
    public function getIsFinalCapture()
    {
        return $this->isFinalCapture;
    }

    /**
     * @param bool $isFinalCapture
     */
    public function setIsFinalCapture($isFinalCapture)
    {
        $this->isFinalCapture = $isFinalCapture;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'amount' => $this->getAmount()->toArray(),
            'is_final_capture' => $this->getIsFinalCapture(),
        ];
    }
}
