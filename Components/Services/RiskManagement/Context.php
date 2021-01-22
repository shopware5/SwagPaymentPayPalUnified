<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\RiskManagement;

class Context
{
    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * @var int|null
     */
    private $sessionProductId;

    /**
     * @var int|null
     */
    private $sessionCategoryId = null;

    /**
     * @var int|null
     */
    private $eventCategoryId;

    /**
     * @param int|null $sessionProductId
     * @param int|null $sessionCategoryId
     * @param int|null $eventCategoryId
     */
    public function __construct(Attribute $attribute, $sessionProductId = null, $sessionCategoryId = null, $eventCategoryId = null)
    {
        $this->attribute = $attribute;
        $this->sessionProductId = $sessionProductId;
        $this->sessionCategoryId = $sessionCategoryId;
        $this->eventCategoryId = $eventCategoryId;
    }

    /**
     * @return Attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @return int|null
     */
    public function getSessionProductId()
    {
        return $this->sessionProductId;
    }

    /**
     * @return int|null
     */
    public function getSessionCategoryId()
    {
        return $this->sessionCategoryId;
    }

    /**
     * @return int|null
     */
    public function getEventCategoryId()
    {
        return $this->eventCategoryId;
    }

    /**
     * @return bool
     */
    public function categoryIdsAreTheSame()
    {
        return $this->sessionCategoryId === $this->eventCategoryId;
    }

    /**
     * @return bool
     */
    public function sessionCategoryIdIsNull()
    {
        return $this->sessionCategoryId === null;
    }

    /**
     * @return bool
     */
    public function eventCategoryIdIsNull()
    {
        return $this->eventCategoryId === null;
    }

    /**
     * @return bool
     */
    public function sessionProductIdIsNull()
    {
        return $this->sessionProductId === null;
    }
}
