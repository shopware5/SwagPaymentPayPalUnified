<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Models\Settings;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="swag_payment_paypal_unified_settings_general")
 */
class General extends ModelEntity
{
    const MERCHANT_LOCATION_GERMANY = 'germany';
    const MERCHANT_LOCATION_OTHER = 'other';

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
     * @ORM\Column(name="brand_name", type="string", length=127)
     */
    private $brandName;

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
     * @ORM\Column(name="use_in_context", type="boolean", nullable=false)
     */
    private $useInContext;

    /**
     * @var string
     * @ORM\Column(name="landing_page_type", type="string")
     */
    private $landingPageType;

    /**
     * @var int
     * @ORM\Column(name="log_level", type="integer")
     */
    private $logLevel;

    /**
     * @var bool
     * @ORM\Column(name="display_errors", type="boolean", nullable=false)
     */
    private $displayErrors;

    /**
     * @var bool
     * @ORM\Column(name="advertise_returns", type="boolean", nullable=false)
     */
    private $advertiseReturns;

    /**
     * @var bool
     * @ORM\Column(name="advertise_installments", type="boolean", nullable=false)
     */
    private $advertiseInstallments;

    /**
     * @var bool
     * @ORM\Column(name="use_smart_payment_buttons", type="boolean", nullable=false)
     */
    private $useSmartPaymentButtons;

    /**
     * @var string
     * @ORM\Column(name="merchant_location", type="string", nullable=false)
     */
    private $merchantLocation;

    /**
     * @var bool
     * @ORM\Column(name="submit_cart", type="boolean", nullable=false)
     */
    private $submitCart;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @param int $logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }

    /**
     * @return bool
     */
    public function getDisplayErrors()
    {
        return $this->displayErrors;
    }

    /**
     * @param bool $displayErrors
     */
    public function setDisplayErrors($displayErrors)
    {
        $this->displayErrors = $displayErrors;
    }

    /**
     * @return bool
     */
    public function getAdvertiseReturns()
    {
        return $this->advertiseReturns;
    }

    /**
     * @param bool $advertiseReturns
     */
    public function setAdvertiseReturns($advertiseReturns)
    {
        $this->advertiseReturns = $advertiseReturns;
    }

    /**
     * @return bool
     */
    public function isAdvertiseInstallments()
    {
        return $this->advertiseInstallments;
    }

    /**
     * @param bool $advertiseInstallments
     */
    public function setAdvertiseInstallments($advertiseInstallments)
    {
        $this->advertiseInstallments = $advertiseInstallments;
    }

    /**
     * @return string
     */
    public function getLandingPageType()
    {
        return $this->landingPageType;
    }

    /**
     * @param string $landingPageType
     */
    public function setLandingPageType($landingPageType)
    {
        $this->landingPageType = $landingPageType;
    }

    /**
     * @return bool
     */
    public function getUseSmartPaymentButtons()
    {
        return $this->useSmartPaymentButtons;
    }

    /**
     * @param bool $useSmartPaymentButtons
     */
    public function setUseSmartPaymentButtons($useSmartPaymentButtons)
    {
        $this->useSmartPaymentButtons = $useSmartPaymentButtons;
    }

    /**
     * @return string
     */
    public function getMerchantLocation()
    {
        return $this->merchantLocation;
    }

    /**
     * @param string $merchantLocation
     */
    public function setMerchantLocation($merchantLocation)
    {
        $this->merchantLocation = $merchantLocation;
    }

    /**
     * @return bool
     */
    public function getSubmitCart()
    {
        return $this->submitCart;
    }

    /**
     * @param bool $submitCart
     */
    public function setSubmitCart($submitCart)
    {
        $this->submitCart = $submitCart;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
