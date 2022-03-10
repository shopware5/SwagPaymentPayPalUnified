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
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Order extends PayPalApiStruct
{
    /**
     * @var string
     */
    protected $intent;

    /**
     * @var array<PurchaseUnit>
     */
    protected $purchaseUnits;

    /**
     * @var ApplicationContext
     */
    protected $applicationContext;

    /**
     * @var PaymentSource|null
     */
    protected $paymentSource;

    /**
     * @var string
     */
    protected $processingInstruction;

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
     * @var Payer
     */
    protected $payer;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var array<Link>
     */
    protected $links;

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
     * @return ApplicationContext
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
     * @return PaymentSource|null
     */
    public function getPaymentSource()
    {
        return $this->paymentSource;
    }

    /**
     * @return void
     */
    public function setPaymentSource(PaymentSource $paymentSource)
    {
        $this->paymentSource = $paymentSource;
    }

    /**
     * @return string|null
     */
    public function getProcessingInstruction()
    {
        return $this->processingInstruction;
    }

    /**
     * @param string $processingInstruction
     *
     * @return void
     */
    public function setProcessingInstruction($processingInstruction)
    {
        $this->processingInstruction = $processingInstruction;
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
     * @return Payer
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
