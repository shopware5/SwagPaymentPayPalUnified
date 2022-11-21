<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Shipping;

use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Tracker extends PayPalApiStruct
{
    const STATUS_SHIPPED = 'SHIPPED';
    const STATUS_CANCELLED = 'CANCELLED';

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $trackingNumber;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $carrier;

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     *
     * @return void
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return string
     */
    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    /**
     * @param string $trackingNumber
     *
     * @return void
     */
    public function setTrackingNumber($trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getCarrier()
    {
        return $this->carrier;
    }

    /**
     * @param string $carrier
     *
     * @return void
     */
    public function setCarrier($carrier)
    {
        $this->carrier = $carrier;
    }

    /**
     * @return array{transaction_id: string, tracking_number: string, status: string, carrier: string}
     */
    public function toArray()
    {
        return [
            'transaction_id' => $this->transactionId,
            'tracking_number' => $this->trackingNumber,
            'status' => $this->status,
            'carrier' => $this->carrier,
        ];
    }
}
