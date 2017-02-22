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

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs;

class Webhook
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $creationTime;

    /**
     * @var string
     */
    private $resourceType;

    /**
     * @var string
     */
    private $eventType;

    /**
     * @var string
     */
    private $summary;

    /**
     * @var array
     */
    private $resource;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCreationTime()
    {
        return $this->creationTime;
    }

    /**
     * @return string
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @return array
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param array $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param array $data
     *
     * @return Webhook
     */
    public static function fromArray(array $data)
    {
        $result = new self();
        $result->setEventType($data['event_type']);
        $result->setCreationTime($data['create_time']);
        $result->setId($data['id']);
        $result->setResourceType($data['resource_type']);
        $result->setSummary($data['summary']);
        $result->setResource($data['resource']);

        return $result;
    }

    /**
     * Converts this object to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * @param string $id
     */
    private function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $creationTime
     */
    private function setCreationTime($creationTime)
    {
        $this->creationTime = $creationTime;
    }

    /**
     * @param string $resourceType
     */
    private function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
    }

    /**
     * @param string $eventType
     */
    private function setEventType($eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * @param string $summary
     */
    private function setSummary($summary)
    {
        $this->summary = $summary;
    }
}
