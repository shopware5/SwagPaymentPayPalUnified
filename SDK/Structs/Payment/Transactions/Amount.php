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

namespace SwagPaymentPayPalUnified\SDK\Structs\Payment\Transactions;

use SwagPaymentPayPalUnified\SDK\Structs\Payment\Transactions\Amount\Details;

class Amount
{
    /**
     * @var string $currency
     */
    private $currency;

    /**
     * @var float $total
     */
    private $total;

    /**
     * @var Details $details
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
     * @return Amount
     */
    public static function fromArray(array $data)
    {
        $result = new Amount();

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
