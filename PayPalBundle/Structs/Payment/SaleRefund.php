<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\Amount;

class SaleRefund
{
    /**
     * @var Amount|null
     */
    private $amount;

    /**
     * @var string
     */
    private $invoiceNumber;

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
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    /**
     * @param string $invoiceNumber
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        //If the amount object is null, we do not need to add it to the array.
        //Note: A sale/capture will be refunded completely in that case
        return $this->getAmount() === null
            ? ['invoice_number' => $this->getInvoiceNumber()]
            : ['invoice_number' => $this->getInvoiceNumber(), 'amount' => $this->getAmount()->toArray()];
    }
}
