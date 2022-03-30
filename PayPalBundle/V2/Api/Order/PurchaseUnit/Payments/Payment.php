<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common\Link;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common\Money;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

abstract class Payment extends PayPalApiStruct
{
    const MAX_LENGTH_INVOICE_ID = 127;
    const MAX_LENGTH_NOTE_TO_PAYER = 255;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var Money|null
     */
    protected $amount;

    /**
     * @var string|null
     */
    protected $customId;

    /**
     * @var Link[]
     */
    protected $links;

    /**
     * @var string
     */
    protected $createTime;

    /**
     * @var string
     */
    protected $updateTime;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common\Money|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param \SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common\Money|null $amount
     *
     * @return void
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string|null
     */
    public function getCustomId()
    {
        return $this->customId;
    }

    /**
     * @param string|null $customId
     *
     * @return void
     */
    public function setCustomId($customId)
    {
        $this->customId = $customId;
    }

    /**
     * @return Link[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param Link[] $links
     *
     * @return void
     */
    public function setLinks(array $links)
    {
        $this->links = $links;
    }

    /**
     * @return string
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * @param string $createTime
     *
     * @return void
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    }

    /**
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

    /**
     * @param string $updateTime
     *
     * @return void
     */
    public function setUpdateTime($updateTime)
    {
        $this->updateTime = $updateTime;
    }
}
