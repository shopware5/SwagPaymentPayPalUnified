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
 *
 * @ORM\Table(name="swag_payment_paypal_unified_settings_general")
 */
class General extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="shop_id", type="string", nullable=false)
     */
    private $shopId;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="string")
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="client_secret", type="string")
     */
    private $clientSecret;

    /**
     * @var string
     *
     * @ORM\Column(name="paypal_payer_id", type="string")
     */
    private $paypalPayerId;

    /**
     * @var string
     *
     * @ORM\Column(name="sandbox_client_id", type="string")
     */
    private $sandboxClientId;

    /**
     * @var string
     *
     * @ORM\Column(name="sandbox_client_secret", type="string")
     */
    private $sandboxClientSecret;

    /**
     * @var string
     *
     * @ORM\Column(name="sandbox_paypal_payer_id", type="string")
     */
    private $sandboxPaypalPayerId;

    /**
     * @var bool
     *
     * @ORM\Column(name="sandbox", type="boolean")
     */
    private $sandbox;

    /**
     * @var bool
     *
     * @ORM\Column(name="show_sidebar_logo", type="boolean", nullable=false)
     */
    private $showSidebarLogo;

    /**
     * @var string
     *
     * @ORM\Column(name="brand_name", type="string", length=127)
     */
    private $brandName;

    /**
     * @var string
     *
     * @ORM\Column(name="order_number_prefix", type="string")
     */
    private $orderNumberPrefix;

    /**
     * @var string
     *
     * @ORM\Column(name="landing_page_type", type="string")
     */
    private $landingPageType;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_errors", type="boolean", nullable=false)
     */
    private $displayErrors;

    /**
     * @var bool
     *
     * @ORM\Column(name="use_smart_payment_buttons", type="boolean", nullable=false)
     */
    private $useSmartPaymentButtons;

    /**
     * @var bool
     *
     * @ORM\Column(name="submit_cart", type="boolean", nullable=false)
     */
    private $submitCart;

    /**
     * @var string
     *
     * @ORM\Column(name="intent", type="string", nullable=false)
     */
    private $intent;

    /**
     * @var string
     *
     * @ORM\Column(name="button_style_color", type="string")
     */
    private $buttonStyleColor;

    /**
     * @var string
     *
     * @ORM\Column(name="button_style_shape", type="string")
     */
    private $buttonStyleShape;

    /**
     * @var string
     *
     * @ORM\Column(name="button_style_size", type="string")
     */
    private $buttonStyleSize;

    /**
     * @var string
     *
     * @ORM\Column(name="button_locale", type="string", length=5)
     */
    private $buttonLocale = '';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getPaypalPayerId()
    {
        return $this->paypalPayerId;
    }

    /**
     * @param string $paypalPayerId
     *
     * @return void
     */
    public function setPaypalPayerId($paypalPayerId)
    {
        $this->paypalPayerId = $paypalPayerId;
    }

    /**
     * @return string
     */
    public function getSandboxClientId()
    {
        return $this->sandboxClientId;
    }

    /**
     * @param string $sandboxClientId
     *
     * @return void
     */
    public function setSandboxClientId($sandboxClientId)
    {
        $this->sandboxClientId = $sandboxClientId;
    }

    /**
     * @return string
     */
    public function getSandboxClientSecret()
    {
        return $this->sandboxClientSecret;
    }

    /**
     * @param string $sandboxClientSecret
     *
     * @return void
     */
    public function setSandboxClientSecret($sandboxClientSecret)
    {
        $this->sandboxClientSecret = $sandboxClientSecret;
    }

    /**
     * @return string
     */
    public function getSandboxPaypalPayerId()
    {
        return $this->sandboxPaypalPayerId;
    }

    /**
     * @param string $sandboxPaypalPayerId
     *
     * @return void
     */
    public function setSandboxPaypalPayerId($sandboxPaypalPayerId)
    {
        $this->sandboxPaypalPayerId = $sandboxPaypalPayerId;
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
     */
    public function setBrandName($brandName)
    {
        $this->brandName = $brandName;
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
     *
     * @return void
     */
    public function setOrderNumberPrefix($orderNumberPrefix)
    {
        $this->orderNumberPrefix = $orderNumberPrefix;
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
     *
     * @return void
     */
    public function setDisplayErrors($displayErrors)
    {
        $this->displayErrors = $displayErrors;
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
     *
     * @return void
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
     *
     * @return void
     */
    public function setUseSmartPaymentButtons($useSmartPaymentButtons)
    {
        $this->useSmartPaymentButtons = $useSmartPaymentButtons;
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
     *
     * @return void
     */
    public function setSubmitCart($submitCart)
    {
        $this->submitCart = $submitCart;
    }

    /**
     * @return string
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * @param string $intent
     *
     * @return void
     */
    public function setIntent($intent)
    {
        $this->intent = $intent;
    }

    /**
     * @return string
     */
    public function getButtonStyleColor()
    {
        return $this->buttonStyleColor;
    }

    /**
     * @param string $buttonStyleColor
     *
     * @return void
     */
    public function setButtonStyleColor($buttonStyleColor)
    {
        $this->buttonStyleColor = $buttonStyleColor;
    }

    /**
     * @return string
     */
    public function getButtonStyleShape()
    {
        return $this->buttonStyleShape;
    }

    /**
     * @param string $buttonStyleShape
     *
     * @return void
     */
    public function setButtonStyleShape($buttonStyleShape)
    {
        $this->buttonStyleShape = $buttonStyleShape;
    }

    /**
     * @return string
     */
    public function getButtonStyleSize()
    {
        return $this->buttonStyleSize;
    }

    /**
     * @param string $buttonStyleSize
     *
     * @return void
     */
    public function setButtonStyleSize($buttonStyleSize)
    {
        $this->buttonStyleSize = $buttonStyleSize;
    }

    /**
     * @return string
     */
    public function getButtonLocale()
    {
        return $this->buttonLocale;
    }

    /**
     * @param string $buttonLocale
     *
     * @return void
     */
    public function setButtonLocale($buttonLocale)
    {
        $this->buttonLocale = $buttonLocale;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return \get_object_vars($this);
    }
}
