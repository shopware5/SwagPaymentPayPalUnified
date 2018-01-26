<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
