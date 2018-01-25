<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingRequest;

class TransactionAmount
{
    /**
     * @var float
     */
    private $value;

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            'value' => $this->getValue(),
            'currency_code' => $this->getCurrencyCode(),
        ];

        return $result;
    }
}
