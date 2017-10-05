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

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions;

class ShipmentDetails
{
    /**
     * @var string
     */
    private $estimatedDeliveryDate;

    /**
     * @return string
     */
    public function getEstimatedDeliveryDate()
    {
        return $this->estimatedDeliveryDate;
    }

    /**
     * @param string $estimatedDeliveryDate
     */
    public function setEstimatedDeliveryDate($estimatedDeliveryDate)
    {
        $this->estimatedDeliveryDate = $estimatedDeliveryDate;
    }

    /**
     * @param array $data
     *
     * @return ShipmentDetails
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

        $result->setEstimatedDeliveryDate($data['estimated_delivery_date']);

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'estimated_delivery_date' => $this->getEstimatedDeliveryDate(),
        ];
    }
}
