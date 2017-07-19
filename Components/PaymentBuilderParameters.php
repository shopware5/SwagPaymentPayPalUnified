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

namespace SwagPaymentPayPalUnified\Components;

class PaymentBuilderParameters
{
    /**
     * @var array
     */
    private $userData;

    /**
     * @var array
     */
    private $basketData;

    /**
     * @var string
     */
    private $webProfileId;

    /**
     * @var string
     */
    private $basketUniqueId;

    /**
     * @var string
     */
    private $paymentType;

    /**
     * @return array
     */
    public function getUserData()
    {
        return $this->userData;
    }

    /**
     * @param array $userData
     */
    public function setUserData($userData)
    {
        $this->userData = $userData;
    }

    /**
     * @return array
     */
    public function getBasketData()
    {
        return $this->basketData;
    }

    /**
     * @param array $basketData
     */
    public function setBasketData($basketData)
    {
        $this->basketData = $basketData;
    }

    /**
     * @return string
     */
    public function getWebProfileId()
    {
        return $this->webProfileId;
    }

    /**
     * @param string $webProfileId
     */
    public function setWebProfileId($webProfileId)
    {
        $this->webProfileId = $webProfileId;
    }

    /**
     * @return string
     */
    public function getBasketUniqueId()
    {
        return $this->basketUniqueId;
    }

    /**
     * @param string $basketUniqueId
     */
    public function setBasketUniqueId($basketUniqueId)
    {
        $this->basketUniqueId = $basketUniqueId;
    }

    /**
     * @return string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    }
}
