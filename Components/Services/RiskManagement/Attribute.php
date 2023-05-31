<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\RiskManagement;

class Attribute
{
    /**
     * @var string|null
     */
    private $attributeName;

    /**
     * @var string|null
     */
    private $attributeValue;

    public function __construct(array $attributeKeyAndValue)
    {
        if (isset($attributeKeyAndValue[0])) {
            $this->attributeName = $attributeKeyAndValue[0];
        }

        if (isset($attributeKeyAndValue[1])) {
            $this->attributeValue = $attributeKeyAndValue[1];
        }
    }

    /**
     * @return string|null
     */
    public function getAttributeName()
    {
        return $this->attributeName;
    }

    /**
     * @return string|null
     */
    public function getAttributeValue()
    {
        return $this->attributeValue;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (\is_string($this->attributeName) && $this->attributeValue !== null) {
            return true;
        }

        return false;
    }
}
