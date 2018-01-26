<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\Amount\Details;

class Amount
{
    /**
     * @var string
     */
    private $currency;

    /**
     * @var float
     */
    private $total;

    /**
     * @var Details
     */
    private $details;

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
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return Details
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param Details $details
     */
    public function setDetails(Details $details)
    {
        $this->details = $details;
    }

    /**
     * @param array $data
     *
     * @return Amount
     */
    public static function fromArray(array $data)
    {
        $result = new self();

        $result->setCurrency($data['currency']);
        $result->setTotal($data['total']);

        if ($data['details'] !== null) {
            $result->setDetails(Details::fromArray($data['details']));
        }

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            'currency' => $this->getCurrency(),
            'total' => $this->getTotal(),
        ];

        if ($this->getDetails() !== null) {
            $result['details'] = $this->getDetails()->toArray();
        }

        return $result;
    }
}
