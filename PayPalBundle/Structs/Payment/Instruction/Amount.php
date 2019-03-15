<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction;

class Amount
{
    /**
     * @var float
     */
    private $value;

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
     * @return Amount
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

        $result->setValue($data['value']);

        return $result;
    }
}
