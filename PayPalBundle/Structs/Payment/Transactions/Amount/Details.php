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
     * @var string
     */
    private $shipping;

    /**
     * @var string
     */
    private $subTotal;

    /**
     * @var string
     */
    private $tax;

    /**
     * @return string
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
        $this->shipping = (string) $shipping;
    }

    /**
     * @return string
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
        $this->subTotal = (string) $subTotal;
    }

    /**
     * @return string
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
        $this->tax = (string) $tax;
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
