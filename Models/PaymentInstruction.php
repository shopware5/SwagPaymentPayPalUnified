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

namespace SwagPaymentPayPalUnified\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Order\Order;

/**
 * This model stores several information about the payment instructions that are being provided from
 * PayPal during a payment execution.
 *
 * @ORM\Entity()
 * @ORM\Table(name="swag_payment_paypal_unified_payment_instruction")
 */
class PaymentInstruction extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $orderNumber
     * @ORM\Column(name="order_number", type="string", nullable=false)
     */
    private $orderNumber;

    /**
     * @var Order $order
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Order")
     * @ORM\JoinColumn(name="order_number", referencedColumnName="ordernumber")
     */
    private $order;

    /**
     * @var string $bankName
     * @ORM\Column(name="bank_name", type="string", nullable=false)
     */
    private $bankName;

    /**
     * @var string $accountHolderName
     * @ORM\Column(name="account_holder", type="string", nullable=false);
     */
    private $accountHolder;

    /**
     * @var string $iban
     * @ORM\Column(name="iban", type="string", nullable=false)
     */
    private $iban;

    /**
     * @var string $bic
     * @ORM\Column(name="bic", type="string", nullable=false)
     */
    private $bic;

    /**
     * @var string $amount
     * @ORM\Column(name="amount", type="string", nullable=false)
     */
    private $amount;

    /**
     * @var string $dueDate
     * @ORM\Column(name="due_date", type="string", nullable=false)
     */
    private $dueDate;

    /**
     * @var string $reference
     * @ORM\Column(name="reference", type="string", nullable=false)
     */
    private $reference;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string
     */
    public function getAccountHolder()
    {
        return $this->accountHolder;
    }

    /**
     * @param string $accountHolder
     */
    public function setAccountHolder($accountHolder)
    {
        $this->accountHolder = $accountHolder;
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     */
    public function setIban($iban)
    {
        $this->iban = $iban;
    }

    /**
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param string $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
