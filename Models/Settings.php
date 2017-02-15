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

namespace SwagPaymentPayPalUnified\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Shop\Shop;

/**
 * @ORM\Entity()
 * @ORM\Table(name="swag_payment_paypal_unified_settings")
 */
class Settings extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="shop_id", type="string", nullable=false)
     */
    private $shopId;

    /**
     * @var Shop
     * @ORM\OneToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     */
    private $shop;

    /**
     * @var string
     * @ORM\Column(name="client_id", type="string")
     */
    private $clientId;

    /**
     * @var string
     * @ORM\Column(name="client_secret", type="string")
     */
    private $clientSecret;

    /**
     * @var bool
     * @ORM\Column(name="sandbox", type="boolean")
     */
    private $sandbox;

    /**
     * @var bool
     * @ORM\Column(name="show_sidebar_logo", type="boolean")
     */
    private $showSidebarLogo;

    /**
     * @var string
     * @ORM\Column(name="brand_name", type="string")
     */
    private $brandName;

    /**
     * @var string
     * @ORM\Column(name="logo_image", type="string")
     */
    private $logoImage;

    /**
     * @var bool
     * @ORM\Column(name="send_order_number", type="boolean", nullable=false)
     */
    private $sendOrderNumber;

    /**
     * @var string
     * @ORM\Column(name="order_number_prefix", type="string")
     */
    private $orderNumberPrefix;

    /**
     * @var bool
     * @ORM\Column(name="plus_active", type="boolean", nullable=false)
     */
    private $plusActive;

    /**
     * @var string
     * @ORM\Column(name="plus_language", type="string")
     */
    private $plusLanguage;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param string $shopId
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * @return Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param Shop $shop
     */
    public function setShop(Shop $shop)
    {
        $this->shop = $shop;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return bool
     */
    public function getSandbox()
    {
        return $this->sandbox;
    }

    /**
     * @param bool $sandbox
     */
    public function setSandbox($sandbox)
    {
        $this->sandbox = $sandbox;
    }

    /**
     * @return bool
     */
    public function getShowSidebarLogo()
    {
        return $this->showSidebarLogo;
    }

    /**
     * @param bool $showSidebarLogo
     */
    public function setShowSidebarLogo($showSidebarLogo)
    {
        $this->showSidebarLogo = $showSidebarLogo;
    }

    /**
     * @return string
     */
    public function getBrandName()
    {
        return $this->brandName;
    }

    /**
     * @param string $brandName
     */
    public function setBrandName($brandName)
    {
        $this->brandName = $brandName;
    }

    /**
     * @return string
     */
    public function getLogoImage()
    {
        return $this->logoImage;
    }

    /**
     * @param string $logoImageId
     */
    public function setLogoImage($logoImageId)
    {
        $this->logoImage = $logoImageId;
    }

    /**
     * @return bool
     */
    public function getSendOrderNumber()
    {
        return $this->sendOrderNumber;
    }

    /**
     * @param bool $sendOrderNumber
     */
    public function setSendOrderNumber($sendOrderNumber)
    {
        $this->sendOrderNumber = $sendOrderNumber;
    }

    /**
     * @return string
     */
    public function getOrderNumberPrefix()
    {
        return $this->orderNumberPrefix;
    }

    /**
     * @param string $orderNumberPrefix
     */
    public function setOrderNumberPrefix($orderNumberPrefix)
    {
        $this->orderNumberPrefix = $orderNumberPrefix;
    }

    /**
     * @return string
     */
    public function getPlusActive()
    {
        return $this->plusActive;
    }

    /**
     * @param string $plusActive
     */
    public function setPlusActive($plusActive)
    {
        $this->plusActive = $plusActive;
    }

    /**
     * @return string
     */
    public function getPlusLanguage()
    {
        return $this->plusLanguage;
    }

    /**
     * @param string $plusLanguage
     */
    public function setPlusLanguage($plusLanguage)
    {
        $this->plusLanguage = $plusLanguage;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
