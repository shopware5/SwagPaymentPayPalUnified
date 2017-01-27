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

namespace SwagPaymentPayPalUnified\SDK\Structs\Payment\Transactions\Amount;

class Details
{
    /**
     * @var float $shipping
     */
    private $shipping;

    /**
     * @var float $subTotal
     */
    private $subTotal;

    /**
     * @var float $tax
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
     * @return Details
     */
    public static function fromArray(array $data = [])
    {
        $result = new Details();

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
            'tax' => $this->getTax()
        ];
    }
}
