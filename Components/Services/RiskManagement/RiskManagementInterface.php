<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\RiskManagement;

interface RiskManagementInterface
{
    const PRODUCT_ID_SESSION_NAME = 'PayPalRiskManagementProductDetailId';
    const CATEGORY_ID_SESSION_NAME = 'PayPalRiskManagementCategoryId';

    /**
     * @param int|null $productId
     * @param int|null $categoryId
     *
     * @return bool
     */
    public function isPayPalNotAllowed($productId = null, $categoryId = null);
}
