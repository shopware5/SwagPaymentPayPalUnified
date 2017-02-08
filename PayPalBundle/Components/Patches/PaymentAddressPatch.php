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

namespace SwagPaymentPayPalUnified\PayPalBundle\Components\Patches;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Payer\PayerInfo\ShippingAddress;

class PaymentAddressPatch implements PatchInterface
{
    const PATH = '/transactions/0/item_list/shipping_address';

    /**
     * @var ShippingAddress
     */
    private $address;

    /**
     * The provided array should contain values for the following keys:
     * [ 'city', 'street', 'zipcode', 'firstname', 'lastname', 'countryiso', 'stateiso' ]
     * in order to create the address patch.
     *
     * @param array $address
     */
    public function __construct(array $address)
    {
        $this->address = new ShippingAddress();
        $this->address->setCity($address['city']);
        $this->address->setLine1($address['street']);
        $this->address->setPostalCode($address['zipcode']);
        $this->address->setRecipientName($address['firstname'] . ' ' . $address['lastname']);
        $this->address->setCountryCode($address['countryiso']);
        $this->address->setState($address['stateiso']);
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return self::OPERATION_ADD;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return self::PATH;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->address->toArray();
    }
}
