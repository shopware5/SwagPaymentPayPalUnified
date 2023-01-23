<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderDataServiceResults;

class OrderAndPaymentStatusResult
{
    /**
     * @var int
     */
    private $orderId;

    /**
     * @var int
     */
    private $orderStatusId;

    /**
     * @var int
     */
    private $paymentStatusId;

    /**
     * @param int $orderId
     * @param int $orderStatusId
     * @param int $paymentStatusId
     */
    public function __construct($orderId, $orderStatusId, $paymentStatusId)
    {
        $this->orderId = $orderId;
        $this->paymentStatusId = $paymentStatusId;
        $this->orderStatusId = $orderStatusId;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return int
     */
    public function getOrderStatusId()
    {
        return $this->orderStatusId;
    }

    /**
     * @return int
     */
    public function getPaymentStatusId()
    {
        return $this->paymentStatusId;
    }
}
