<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Sale\SaleType;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Sale\State;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Sale\StateReasonCode;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Sale\TransactionFee;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\Amount;

class Sale
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $invoiceNumber;

    /**
     * @var string
     */
    private $createTime;

    /**
     * @var string
     */
    private $updateTime;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var string
     */
    private $parentPayment;

    /**
     * @var string
     *
     * @see State
     */
    private $state;

    /**
     * @var string
     *
     * @see StateReasonCode
     */
    private $reasonCode;

    /**
     * @var string
     *
     * @see SaleType
     */
    private $type;

    /**
     * @var TransactionFee
     */
    private $transactionFee;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return string
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * @param string $createTime
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    }

    /**
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

    /**
     * @param string $updateTime
     */
    public function setUpdateTime($updateTime)
    {
        $this->updateTime = $updateTime;
    }

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
     * @return string
     */
    public function getParentPayment()
    {
        return $this->parentPayment;
    }

    /**
     * @param string $parentPayment
     */
    public function setParentPayment($parentPayment)
    {
        $this->parentPayment = $parentPayment;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * @param string $reasonCode
     */
    public function setReasonCode($reasonCode)
    {
        $this->reasonCode = $reasonCode;
    }

    /**
     * @return TransactionFee
     */
    public function getTransactionFee()
    {
        return $this->transactionFee;
    }

    /**
     * @param TransactionFee $transactionFee
     */
    public function setTransactionFee($transactionFee)
    {
        $this->transactionFee = $transactionFee;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param array  $data
     * @param string $type
     *
     * @see SaleType
     *
     * @return Sale
     */
    public static function fromArray(array $data, $type = SaleType::SALE)
    {
        $result = new self();
        $result->setUpdateTime($data['update_time']);
        $result->setCreateTime($data['create_time']);
        $result->setState($data['state']);
        $result->setAmount(Amount::fromArray($data['amount']));
        $result->setParentPayment($data['parent_payment']);
        $result->setReasonCode($data['reason_code']);
        $result->setType($type);
        $result->setInvoiceNumber($data['invoice_number']);
        $result->setId($data['id']);

        if ($data['transaction_fee']) {
            $result->setTransactionFee(TransactionFee::fromArray($data['transaction_fee']));
        }

        return $result;
    }
}
