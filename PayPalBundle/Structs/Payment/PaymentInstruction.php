<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\PaymentInstructionType;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\RecipientBanking;

class PaymentInstruction
{
    /**
     * @var string
     */
    private $referenceNumber;

    /**
     * @var RecipientBanking
     */
    private $recipientBanking;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var string
     *
     * @see PaymentInstructionType
     */
    private $type;

    /**
     * @var string
     */
    private $dueDate;

    /**
     * @return string
     */
    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }

    /**
     * @param string $referenceNumber
     */
    public function setReferenceNumber($referenceNumber)
    {
        $this->referenceNumber = $referenceNumber;
    }

    /**
     * @return RecipientBanking
     */
    public function getRecipientBanking()
    {
        return $this->recipientBanking;
    }

    /**
     * @param RecipientBanking $recipientBanking
     */
    public function setRecipientBanking(RecipientBanking $recipientBanking)
    {
        $this->recipientBanking = $recipientBanking;
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
    public function setAmount(Amount $amount)
    {
        $this->amount = $amount;
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
     * @param array $data
     *
     * @return PaymentInstruction
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

        $result->setReferenceNumber($data['reference_number']);
        $result->setRecipientBanking(RecipientBanking::fromArray($data['recipient_banking_instruction']));
        $result->setAmount(Amount::fromArray($data['amount']));
        $result->setDueDate($data['payment_due_date']);
        $result->setType($data['instruction_type']);

        return $result;
    }
}
