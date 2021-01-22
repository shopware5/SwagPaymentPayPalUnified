<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\RiskManagement;

interface RiskManagementHelperInterface
{
    /**
     * @param string|null $attributeRule
     *
     * @return Attribute
     */
    public function createAttribute($attributeRule = null);

    /**
     * @param int $eventCategoryId
     *
     * @return Context
     */
    public function createContext(Attribute $attribute, $eventCategoryId = null);

    /**
     * @return bool
     */
    public function isProductInCategory(Context $context);

    /**
     * @return bool
     */
    public function isCategoryAmongTheParents(Context $context);

    /**
     * @return bool
     */
    public function hasProductAttributeValue(Context $context);

    /**
     * @return array
     */
    public function getProductOrdernumbersMatchedAttribute(Context $context);

    /**
     * @return array
     */
    public function getProductOrdernumbersNotMatchedAttribute(Context $context);
}
