<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Common;

class CustomerHelper
{
    const CUSTOMER_GROUP_USE_GROSS_PRICES = 'customerGroupUseGrossPrices';

    /**
     * Returns a value indicating whether the current customer
     * uses the net price instead of the gross price.
     *
     * The naming of this method is off from the parameter name, because it
     * behaves like this:
     *
     * Basic settings -> [customer group] -> Show gross price in shop unchecked
     *   - show_net: false
     * Basic settings -> [customer group] -> Show gross price in shop checked
     *   - show_net: true
     *
     * So the parameter name is wrong in the core.
     *
     * @param array<string,mixed> $customer
     *
     * @return bool
     */
    public function usesGrossPrice(array $customer)
    {
        return (bool) $customer['additional']['show_net'];
    }

    /**
     * Returns a value indicating whether any VAT will be charged.
     *
     * @param array<string,mixed> $customer
     *
     * @return bool
     */
    public function chargeVat(array $customer)
    {
        return (bool) $customer['additional']['charge_vat'];
    }

    /**
     * Returns a value indicating whether or not only the net prices without
     * any tax should be used in the total amount object.
     *
     * @return bool
     */
    public function hasNetPriceCaluclationIndicator(array $customer)
    {
        if (!empty($customer['additional']['countryShipping']['taxfree'])) {
            return true;
        }

        if (empty($customer['additional']['countryShipping']['taxfree_ustid'])) {
            return false;
        }

        if (!empty($customer['shippingaddress']['ustid'])
            && !empty($customer['additional']['country']['taxfree_ustid'])) {
            return true;
        }

        if (empty($customer['shippingaddress']['ustid'])) {
            return false;
        }

        if ($customer[self::CUSTOMER_GROUP_USE_GROSS_PRICES]) {
            return false;
        }

        return true;
    }
}
