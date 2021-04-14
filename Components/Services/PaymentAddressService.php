<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Common\Address;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Payer\PayerInfo;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ItemList\ShippingAddress;

class PaymentAddressService
{
    /**
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
     * @return Address
     */
    public function getBillingAddress(array $userData)
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
