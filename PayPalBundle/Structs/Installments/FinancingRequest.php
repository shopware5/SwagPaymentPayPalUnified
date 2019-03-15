<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingRequest\TransactionAmount;

class FinancingRequest
{
    /**
     * @var string
     */
    private $financingCountryCode;

    /**
     * @var TransactionAmount
     */
    private $transactionAmount;

    /**
     * @return string
     */
    public function getFinancingCountryCode()
    {
        return $this->financingCountryCode;
    }

    /**
     * @param string $financingCountryCode
     */
    public function setFinancingCountryCode($financingCountryCode)
    {
        $this->financingCountryCode = $financingCountryCode;
    }

    /**
     * @return TransactionAmount
     */
    public function getTransactionAmount()
    {
        return $this->transactionAmount;
    }

    public function setTransactionAmount(TransactionAmount $transactionAmount)
    {
        $this->transactionAmount = $transactionAmount;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'financing_country_code' => $this->getFinancingCountryCode(),
            'transaction_amount' => $this->getTransactionAmount()->toArray(),
        ];
    }
}
