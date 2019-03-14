<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ItemList;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\RelatedResources;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ShipmentDetails;

class Transactions
{
    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var ItemList
     */
    private $itemList;

    /**
     * @var RelatedResources
     */
    private $relatedResources;

    /**
     * @var ShipmentDetails
     */
    private $shipmentDetails;

    /**
     * @return Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount(Amount $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return ItemList
     */
    public function getItemList()
    {
        return $this->itemList;
    }

    /**
     * @param $itemList
     */
    public function setItemList($itemList)
    {
        $this->itemList = $itemList;
    }

    /**
     * @return RelatedResources
     */
    public function getRelatedResources()
    {
        return $this->relatedResources;
    }

    public function setRelatedResources(RelatedResources $relatedResources)
    {
        $this->relatedResources = $relatedResources;
    }

    /**
     * @return ShipmentDetails
     */
    public function getShipmentDetails()
    {
        return $this->shipmentDetails;
    }

    /**
     * @param ShipmentDetails $shipmentDetails
     */
    public function setShipmentDetails($shipmentDetails)
    {
        $this->shipmentDetails = $shipmentDetails;
    }

    /**
     * @return Transactions
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

        if ($data['amount']) {
            $result->setAmount(Amount::fromArray($data['amount']));

            if (!empty($data['item_list'])) {
                $result->setItemList(ItemList::fromArray($data['item_list']));
            }
        }

        if ($data['related_resources']) {
            $result->setRelatedResources(RelatedResources::fromArray($data['related_resources']));
        }

        if ($data['shipment_details']) {
            $result->setShipmentDetails(ShipmentDetails::fromArray($data['shipment_details']));
        }

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            'amount' => $this->getAmount()->toArray(),
        ];

        if ($this->getShipmentDetails() !== null) {
            $result['shipment_details'] = $this->getShipmentDetails()->toArray();
        }

        if ($this->getItemList() === null) {
            return $result;
        }

        $result['item_list'] = $this->getItemList()->toArray();

        return $result;
    }
}
