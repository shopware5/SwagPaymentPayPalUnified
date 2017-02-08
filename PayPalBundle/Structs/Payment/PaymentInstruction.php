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

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\RecipientBanking;

class PaymentInstruction
{
    /** @var string $paymentMethod */
    private $referenceNumber;

    /** @var RecipientBanking $recipientBanking */
    private $recipientBanking;

    /** @var Amount */
    private $amount;

    /** @var string $type */
    private $type;

    /** @var string $dueDate */
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
