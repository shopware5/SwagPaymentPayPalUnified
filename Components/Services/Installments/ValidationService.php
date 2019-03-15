<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Installments;

class ValidationService
{
    /**
     * @param float $productPrice
     *
     * @return bool
     */
    public function validatePrice($productPrice)
    {
        return $productPrice >= 99 && $productPrice <= 5000;
    }

    /**
     * @return bool
     */
    public function validateCustomer(array $customerData)
    {
        //Check if the customer belongs to a company
        if (!empty($customerData['billingaddress']['company'])) {
            return false;
        }

        //Check if the customer is german
        if ($customerData['additional']['country']['countryiso'] !== 'DE') {
            return false;
        }

        return true;
    }
}
