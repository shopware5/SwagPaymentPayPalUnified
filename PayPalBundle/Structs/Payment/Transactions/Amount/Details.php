<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\Amount;

class Details
{
    /**
     * @var float
     */
    private $shipping;

    /**
     * @var float
     */
    private $subTotal;

    /**
     * @var float
     */
    private $tax;

    /**
     * @return float
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param float $shipping
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return float
     */
    public function getSubTotal()
    {
        return $this->subTotal;
    }

    /**
     * @param float $subTotal
     */
    public function setSubTotal($subTotal)
    {
        $this->subTotal = $subTotal;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param float $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @param array $data
     *
     * @return Details
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

        $result->setShipping($data['shipping']);
        $result->setTax($data['tax']);
        $result->setSubTotal($data['subtotal']);

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'shipping' => $this->getShipping(),
            'subtotal' => $this->getSubTotal(),
            'tax' => $this->getTax(),
        ];
    }
}
