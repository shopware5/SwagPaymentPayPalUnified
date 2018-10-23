<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Installments;

/**
 * Interface ValidationServiceInterface
 * @package SwagPaymentPayPalUnified\Components\Services\Installments
 */
interface ValidationServiceInterface
{
    /**
     * @param float $productPrice
     *
     * @return bool
     */
    public function validatePrice($productPrice);

    /**
     * @param array $customerData
     *
     * @return bool
     */
    public function validateCustomer(array $customerData);
}
