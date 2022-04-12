<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\AbstractAmount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payee;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class PurchaseUnit extends PayPalApiStruct
{
    /**
     * @var string
     */
    protected $referenceId;

    /**
     * @var AbstractAmount
     */
    protected $amount;

    /**
     * @var Payee
     */
    protected $payee;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string|null
     */
    protected $customId;

    /**
     * @var string|null
     */
    protected $invoiceId;

    /**
     * @var array<Item>|null
     */
    protected $items;

    /**
     * @var Shipping|null
     */
    protected $shipping;

    /**
     * @var Payments
     */
    protected $payments;

    /**
     * @return string
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * @param string $referenceId
     *
     * @return void
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;
    }

    /**
     * @return AbstractAmount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param AbstractAmount $amount
     *
     * @return void
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return Payee
     */
    public function getPayee()
    {
        return $this->payee;
    }

    /**
     * @param Payee $payee
     *
     * @return void
     */
    public function setPayee($payee)
    {
        $this->payee = $payee;
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
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getCustomId()
    {
        return $this->customId;
    }

    /**
     * @param string|null $customId
     *
     * @return void
     */
    public function setCustomId($customId)
    {
        $this->customId = $customId;
    }

    /**
     * @return string|null
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * @param string|null $invoiceId
     *
     * @return void
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    /**
     * @return array<Item>|null
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array<Item>|null $items
     *
     * @return void
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return Shipping|null
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param Shipping|null $shipping
     *
     * @return void
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return Payments
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param Payments $payments
     *
     * @return void
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
    }
}
