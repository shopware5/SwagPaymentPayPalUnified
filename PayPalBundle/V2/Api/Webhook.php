<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Refund;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Webhook\Link;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Webhook extends PayPalApiStruct
{
    const RESOURCE_TYPE_AUTHORIZATION = 'authorization';
    const RESOURCE_TYPE_CAPTURE = 'capture';
    const RESOURCE_TYPE_REFUND = 'refund';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $createTime;

    /**
     * @var string
     */
    protected $resourceType;

    /**
     * @var string
     */
    protected $eventType;

    /**
     * @var string
     */
    protected $summary;

    /**
     * @var Authorization|Capture|Refund|null
     */
    protected $resource;

    /**
     * @var Link[]
     */
    protected $links;

    /**
     * @var string
     */
    protected $eventVersion;

    /**
     * @var string
     */
    protected $resourceVersion;

    /**
     * @return static
     */
    public function assign(array $arrayDataWithSnakeCaseKeys)
    {
        $resourceData = $arrayDataWithSnakeCaseKeys['resource'];
        unset($arrayDataWithSnakeCaseKeys['resource']);
        $webhook = parent::assign($arrayDataWithSnakeCaseKeys);

        $resourceClass = $this->identifyResourceType($arrayDataWithSnakeCaseKeys['resource_type']);
        if ($resourceClass !== null) {
            /** @var Authorization|Capture|Refund $resource */
            $resource = new $resourceClass();
            $resource->assign($resourceData);

            $webhook->setResource($resource);
        }

        return $webhook;
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
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @param string $resourceType
     *
     * @return void
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
    }

    /**
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @param string $eventType
     *
     * @return void
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     *
     * @return void
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * @return Payment|null
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param Authorization|Capture|Refund $resource
     *
     * @return void
     */
    public function setResource(Payment $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return mixed[]
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
    public function getEventVersion()
    {
        return $this->eventVersion;
    }

    /**
     * @param string $eventVersion
     *
     * @return void
     */
    public function setEventVersion($eventVersion)
    {
        $this->eventVersion = $eventVersion;
    }

    /**
     * @return string
     */
    public function getResourceVersion()
    {
        return $this->resourceVersion;
    }

    /**
     * @param string $resourceVersion
     *
     * @return void
     */
    public function setResourceVersion($resourceVersion)
    {
        $this->resourceVersion = $resourceVersion;
    }

    /**
     * @param string $resourceType
     *
     * @return class-string|null
     */
    protected function identifyResourceType($resourceType)
    {
        switch ($resourceType) {
            case self::RESOURCE_TYPE_AUTHORIZATION:
                return Authorization::class;
            case self::RESOURCE_TYPE_CAPTURE:
                return Capture::class;
            case self::RESOURCE_TYPE_REFUND:
                return Refund::class;
            default:
                return null;
        }
    }
}
