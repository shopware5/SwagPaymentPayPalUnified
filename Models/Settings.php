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
     * @var bool
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

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
     * @ORM\Column(name="show_sidebar_logo", type="boolean", nullable=false)
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
     * @ORM\Column(name="use_in_context", type="boolean", nullable=false )
     */
    private $useInContext;

    /**
     * @var int
     * @ORM\Column(name="paypal_payment_intent", type="integer")
     */
    private $paypalPaymentIntent;

    /**
     * @var string
     * @ORM\Column(name="web_profile_id", type="string")
     */
    private $webProfileId;

    /**
     * @var string
     * @ORM\Column(name="web_profile_id_ec", type="string")
     */
    private $webProfileIdEc;

    /**
     * @var bool
     * @ORM\Column(name="plus_active", type="boolean", nullable=false)
     */
    private $plusActive;

    /**
     * @var bool
     * @ORM\Column(name="plus_restyle", type="boolean", nullable=false)
     */
    private $plusRestyle;

    /**
     * @var string
     * @ORM\Column(name="plus_language", type="string")
     */
    private $plusLanguage;

    /**
     * @var bool
     * @ORM\Column(name="installments_active", type="boolean", nullable=false)
     */
    private $installmentsActive;

    /**
     * @var int
     * @ORM\Column(name="installments_presentment_detail", type="integer")
     */
    private $installmentsPresentmentDetail;

    /**
     * @var int
     * @ORM\Column(name="installments_presentment_cart", type="integer")
     */
    private $installmentsPresentmentCart;

    /**
     * @var bool
     * @ORM\Column(name="installments_show_logo", type="boolean", nullable=false)
     */
    private $installmentsShowLogo;

    /**
     * @var bool
     * @ORM\Column(name="ec_active", type="boolean", nullable=false)
     */
    private $ecActive;

    /**
     * @var bool
     * @ORM\Column(name="ec_detail_active", type="boolean", nullable=false)
     */
    private $ecDetailActive;

    /**
     * @var string
     * @ORM\Column(name="ec_button_style_color", type="string")
     */
    private $ecButtonStyleColor;

    /**
     * @var string
     * @ORM\Column(name="ec_button_style_shape", type="string")
     */
    private $ecButtonStyleShape;

    /**
     * @var string
     * @ORM\Column(name="ec_button_style_size", type="string")
     */
    private $ecButtonStyleSize;

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
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
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
     * @return bool
     */
    public function getUseInContext()
    {
        return $this->useInContext;
    }

    /**
     * @param bool $useInContext
     */
    public function setUseInContext($useInContext)
    {
        $this->useInContext = $useInContext;
    }

    /**
     * @return int
     */
    public function getPaypalPaymentIntent()
    {
        return $this->paypalPaymentIntent;
    }

    /**
     * @param int $paypalPaymentIntent
     */
    public function setPaypalPaymentIntent($paypalPaymentIntent)
    {
        $this->paypalPaymentIntent = $paypalPaymentIntent;
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
    public function getWebProfileIdEc()
    {
        return $this->webProfileIdEc;
    }

    /**
     * @param string $webProfileIdEc
     */
    public function setWebProfileIdEc($webProfileIdEc)
    {
        $this->webProfileIdEc = $webProfileIdEc;
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
     * @return bool
     */
    public function getPlusRestyle()
    {
        return $this->plusRestyle;
    }

    /**
     * @param bool $plusRestyle
     */
    public function setPlusRestyle($plusRestyle)
    {
        $this->plusRestyle = $plusRestyle;
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
     * @return bool
     */
    public function getInstallmentsActive()
    {
        return $this->installmentsActive;
    }

    /**
     * @param bool $installmentsActive
     */
    public function setInstallmentsActive($installmentsActive)
    {
        $this->installmentsActive = $installmentsActive;
    }

    /**
     * @return int
     */
    public function getInstallmentsPresentmentDetail()
    {
        return $this->installmentsPresentmentDetail;
    }

    /**
     * @param int $installmentsPresentmentDetail
     */
    public function setInstallmentsPresentmentDetail($installmentsPresentmentDetail)
    {
        $this->installmentsPresentmentDetail = $installmentsPresentmentDetail;
    }

    /**
     * @return int
     */
    public function getInstallmentsPresentmentCart()
    {
        return $this->installmentsPresentmentCart;
    }

    /**
     * @param int $installmentsPresentmentCart
     */
    public function setInstallmentsPresentmentCart($installmentsPresentmentCart)
    {
        $this->installmentsPresentmentCart = $installmentsPresentmentCart;
    }

    /**
     * @return bool
     */
    public function getInstallmentsShowLogo()
    {
        return $this->installmentsShowLogo;
    }

    /**
     * @param bool $installmentsShowLogo
     */
    public function setInstallmentsShowLogo($installmentsShowLogo)
    {
        $this->installmentsShowLogo = $installmentsShowLogo;
    }

    /**
     * @return bool
     */
    public function getEcActive()
    {
        return $this->ecActive;
    }

    /**
     * @param bool $ecActive
     */
    public function setEcActive($ecActive)
    {
        $this->ecActive = $ecActive;
    }

    /**
     * @return bool
     */
    public function getEcDetailActive()
    {
        return $this->ecDetailActive;
    }

    /**
     * @param bool $ecDetailActive
     */
    public function setEcDetailActive($ecDetailActive)
    {
        $this->ecDetailActive = $ecDetailActive;
    }

    /**
     * @return mixed
     */
    public function getEcButtonStyleColor()
    {
        return $this->ecButtonStyleColor;
    }

    /**
     * @param mixed $ecButtonStyleColor
     */
    public function setEcButtonStyleColor($ecButtonStyleColor)
    {
        $this->ecButtonStyleColor = $ecButtonStyleColor;
    }

    /**
     * @return string
     */
    public function getEcButtonStyleShape()
    {
        return $this->ecButtonStyleShape;
    }

    /**
     * @param string $ecButtonStyleShape
     */
    public function setEcButtonStyleShape($ecButtonStyleShape)
    {
        $this->ecButtonStyleShape = $ecButtonStyleShape;
    }

    /**
     * @return string
     */
    public function getEcButtonStyleSize()
    {
        return $this->ecButtonStyleSize;
    }

    /**
     * @param string $ecButtonStyleSize
     */
    public function setEcButtonStyleSize($ecButtonStyleSize)
    {
        $this->ecButtonStyleSize = $ecButtonStyleSize;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
