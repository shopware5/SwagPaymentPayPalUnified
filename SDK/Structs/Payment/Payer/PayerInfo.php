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

namespace SwagPaymentPayPalUnified\SDK\Structs\Payment\Payer;

use SwagPaymentPayPalUnified\SDK\Structs\Payment\Payer\PayerInfo\ShippingAddress;

class PayerInfo
{
    /** @var string $email */
    private $email;

    /** @var string $firstName */
    private $firstName;

    /** @var string $lastName */
    private $lastName;

    /** @var string $payerId */
    private $payerId;

    /** @var string $phone */
    private $phone;

    /** @var string $countryCode */
    private $countryCode;

    /** @var ShippingAddress $shippingAddress */
    private $shippingAddress;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getPayerId()
    {
        return $this->payerId;
    }

    /**
     * @param string $payerId
     */
    public function setPayerId($payerId)
    {
        $this->payerId = $payerId;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return ShippingAddress
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param ShippingAddress $shippingAddress
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @param array|null $data
     * @return PayerInfo
     */
    public static function fromArray(array $data = null)
    {
        $result = new PayerInfo();

        if ($data === null) {
            return $result;
        }

        $result->setCountryCode($data['country_code']);
        $result->setEmail($data['email']);
        $result->setFirstName($data['first_name']);
        $result->setLastName($data['last_name']);
        $result->setPayerId($data['payer_id']);
        $result->setPhone($data['phone']);
        $result->setShippingAddress(ShippingAddress::fromArray($data['shipping_address']));

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            'country_code' => $this->getCountryCode(),
            'email' => $this->getEmail(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'payer_id' => $this->getPayerId(),
            'phone' => $this->getPhone(),
        ];

        if ($this->shippingAddress !== null) {
            $result['shipping_address'] = $this->shippingAddress->toArray();
        }

        return $result;
    }
}
