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

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Common\Link;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\Amount;

abstract class RelatedResource
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $parentPayment;

    /**
     * @var Link[]
     */
    private $links;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $createTime;

    /**
     * @var string
     */
    private $updateTime;

    /**
     * @return Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param Amount $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getParentPayment()
    {
        return $this->parentPayment;
    }

    /**
     * @param string $parentPayment
     */
    public function setParentPayment($parentPayment)
    {
        $this->parentPayment = $parentPayment;
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
     */
    public function setLinks($links)
    {
        $this->links = $links;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
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
     */
    public function setUpdateTime($updateTime)
    {
        $this->updateTime = $updateTime;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param RelatedResource       $resource
     * @param array|RelatedResource $data
     * @param string                $type
     *
     * @see ResourceType
     */
    protected function prepare(RelatedResource $resource, array $data, $type)
    {
        $resource->setAmount(Amount::fromArray($data['amount']));
        $resource->setId($data['id']);
        $resource->setState($data['state']);
        $resource->setParentPayment($data['parent_payment']);
        $resource->setCreateTime($data['create_time']);
        $resource->setUpdateTime($data['update_time']);
        $resource->setType($type);

        $links = [];
        foreach ($data['links'] as $link) {
            $links[] = Link::fromArray($link);
        }

        $resource->setLinks($links);
    }

    /**
     * @param string $type
     */
    private function setType($type)
    {
        $this->type = $type;
    }
}
