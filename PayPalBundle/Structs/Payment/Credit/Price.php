<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Credit;

class Price
{
    /**
     * @var float
     */
    private $value;

    /**
     * @var string
     */
    private $currency;

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
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return Price
     */
    public static function fromArray(array $data)
    {
        $result = new self();
        $result->setValue($data['value']);
        $result->setCurrency($data['currency']);

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'value' => $this->getValue(),
            'currency' => $this->getCurrency(),
        ];
    }
}
