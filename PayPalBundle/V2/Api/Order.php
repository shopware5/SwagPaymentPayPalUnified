<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\ApplicationContext;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Link;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Order extends PayPalApiStruct
{
    /**
     * @var string
     */
    protected $createTime;

    /**
     * @var string
     */
    protected $updateTime;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $intent;

    /**
     * @var Payer
     */
    protected $payer;

    /**
     * @var PurchaseUnit[]
     */
    protected $purchaseUnits;

    /**
     * @var ApplicationContext
     */
    protected $applicationContext;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var Link[]
     */
    protected $links;

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
     * @return string
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * @param string $intent
     *
     * @return void
     */
    public function setIntent($intent)
    {
        $this->intent = $intent;
    }

    /**
     * @return \SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer
     */
    public function getPayer()
    {
        return $this->payer;
    }

    /**
     * @return void
     */
    public function setPayer(Payer $payer)
    {
        $this->payer = $payer;
    }

    /**
     * @return PurchaseUnit[]
     */
    public function getPurchaseUnits()
    {
        return $this->purchaseUnits;
    }

    /**
     * @param PurchaseUnit[] $purchaseUnits
     *
     * @return void
     */
    public function setPurchaseUnits(array $purchaseUnits)
    {
        $this->purchaseUnits = $purchaseUnits;
    }

    /**
     * @return \SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\ApplicationContext
     */
    public function getApplicationContext()
    {
        return $this->applicationContext;
    }

    /**
     * @return void
     */
    public function setApplicationContext(ApplicationContext $applicationContext)
    {
        $this->applicationContext = $applicationContext;
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
}
