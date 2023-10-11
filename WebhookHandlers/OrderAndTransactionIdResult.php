<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\WebhookHandlers;

class OrderAndTransactionIdResult
{
    /**
     * @var string
     */
    private $orderId;

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @param string $orderId
     * @param string $transactionId
     */
    public function __construct($orderId, $transactionId)
    {
        $this->orderId = $orderId;
        $this->transactionId = $transactionId;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }
}
