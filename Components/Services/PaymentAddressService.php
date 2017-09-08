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

namespace SwagPaymentPayPalUnified\Components\Services;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Common\Address;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Payer\PayerInfo;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ItemList\ShippingAddress;

class PaymentAddressService
{
    /**
     * @param array $userData
     *
     * @return ShippingAddress
     */
    public function getShippingAddress(array $userData)
    {
        $shippingAddress = $userData['shippingaddress'];
        $shippingAddress['countryiso'] = $userData['additional']['countryShipping']['countryiso'];
        $shippingAddress['stateiso'] = $userData['additional']['stateShipping']['shortcode'];

        $address = new ShippingAddress();
        $address->setCity($shippingAddress['city']);
        $address->setLine1($shippingAddress['street']);
        $address->setPostalCode($shippingAddress['zipcode']);
        $address->setRecipientName($shippingAddress['firstname'] . ' ' . $shippingAddress['lastname']);
        $address->setCountryCode($shippingAddress['countryiso']);
        $address->setState($shippingAddress['stateiso']);

        return $address;
    }

    /**
     * @param array $userData
     *
     * @return PayerInfo
     */
    public function getPayerInfo(array $userData)
    {
        $payerInfo = new PayerInfo();
        $payerInfo->setBillingAddress($this->getBillingAddress($userData));
        $payerInfo->setEmail($userData['additional']['user']['email']);
        $payerInfo->setCountryCode($userData['additional']['country']['countryiso']);
        $payerInfo->setFirstName($userData['billingaddress']['firstname']);
        $payerInfo->setLastName($userData['billingaddress']['lastname']);
        $payerInfo->setPhone($userData['billingaddress']['phone']);

        return $payerInfo;
    }

    /**
     * @param array $userData
     *
     * @return Address
     */
    private function getBillingAddress(array $userData)
    {
        $billingAddress = $userData['billingaddress'];
        $billingAddress['countryiso'] = $userData['additional']['country']['countryiso'];
        $billingAddress['stateiso'] = $userData['additional']['state']['shortcode'];

        $address = new Address();
        $address->setCity($billingAddress['city']);
        $address->setLine1($billingAddress['street']);
        $address->setPostalCode($billingAddress['zipcode']);
        $address->setCountryCode($billingAddress['countryiso']);
        $address->setState($billingAddress['stateiso']);

        return $address;
    }
}
