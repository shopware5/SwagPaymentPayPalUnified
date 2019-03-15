<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction;

class RecipientBanking
{
    /**
     * @var string
     */
    private $bankName;

    /**
     * @var string
     */
    private $accountHolderName;

    /**
     * @var string
     */
    private $accountNumber;

    /**
     * @var string
     */
    private $iban;

    /**
     * @var string
     */
    private $bic;

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
    public function getAccountHolderName()
    {
        return $this->accountHolderName;
    }

    /**
     * @param string $accountHolderName
     */
    public function setAccountHolderName($accountHolderName)
    {
        $this->accountHolderName = $accountHolderName;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
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
     * @return RecipientBanking
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

        $result->setAccountHolderName($data['account_holder_name']);
        $result->setAccountNumber($data['account_number']);
        $result->setBankName($data['bank_name']);
        $result->setBic($data['bank_identifier_code']);
        $result->setIban($data['international_bank_account_number']);

        return $result;
    }
}
