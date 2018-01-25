<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse\FinancingOption;

class Amount
{
    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @var float
     */
    private $value;

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
     * @return array
     */
    public function toArray()
    {
        return [
            'currencyCode' => $this->getCurrencyCode(),
            'value' => $this->getValue(),
        ];
    }

    /**
     * @param array $data
     *
     * @return Amount
     */
    public static function fromArray(array $data = [])
    {
        $amount = new self();
        $amount->setCurrencyCode($data['currency_code']);
        $amount->setValue((float) $data['value']);

        return $amount;
    }
}
