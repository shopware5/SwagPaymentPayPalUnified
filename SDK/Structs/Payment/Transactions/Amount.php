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

use SwagPaymentPayPalUnified\SDK\Structs\Payment\Transactions\Amount\AmountDetails;

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
     * @var AmountDetails $details
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
     * @return AmountDetails
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param AmountDetails $details
     */
    public function setDetails(AmountDetails $details)
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
        $result->setDetails(AmountDetails::fromArray($data['details']));

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'currency' => $this->getCurrency(),
            'total' => $this->getTotal(),
            'details' => $this->getDetails()->toArray()
        ];
    }
}
