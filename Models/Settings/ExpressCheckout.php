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
 * @ORM\Table(name="swag_payment_paypal_unified_settings_express")
 */
class ExpressCheckout extends ModelEntity
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
     * @ORM\Column(name="detail_active", type="boolean", nullable=false)
     */
    private $detailActive;

    /**
     * @var bool
     *
     * @ORM\Column(name="cart_active", type="boolean", nullable=false)
     */
    private $cartActive;

    /**
     * @var bool
     *
     * @ORM\Column(name="off_canvas_active", type="boolean", nullable=false)
     */
    private $offCanvasActive;

    /**
     * @var bool
     *
     * @ORM\Column(name="login_active", type="boolean", nullable=false)
     */
    private $loginActive;

    /**
     * @var bool
     *
     * @ORM\Column(name="listing_active", type="boolean", nullable=false)
     */
    private $listingActive;

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
     * @var bool
     *
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
    public function getDetailActive()
    {
        return $this->detailActive;
    }

    /**
     * @param bool $detailActive
     *
     * @return void
     */
    public function setDetailActive($detailActive)
    {
        $this->detailActive = $detailActive;
    }

    /**
     * @return bool
     */
    public function getCartActive()
    {
        return $this->cartActive;
    }

    /**
     * @param bool $cartActive
     *
     * @return void
     */
    public function setCartActive($cartActive)
    {
        $this->cartActive = $cartActive;
    }

    /**
     * @return bool
     */
    public function getOffCanvasActive()
    {
        return $this->offCanvasActive;
    }

    /**
     * @param bool $offCanvasActive
     *
     * @return void
     */
    public function setOffCanvasActive($offCanvasActive)
    {
        $this->offCanvasActive = $offCanvasActive;
    }

    /**
     * @return bool
     */
    public function getLoginActive()
    {
        return $this->loginActive;
    }

    /**
     * @param bool $loginActive
     *
     * @return void
     */
    public function setLoginActive($loginActive)
    {
        $this->loginActive = $loginActive;
    }

    /**
     * @return bool
     */
    public function getListingActive()
    {
        return $this->listingActive;
    }

    /**
     * @param bool $listingActive
     *
     * @return void
     */
    public function setListingActive($listingActive)
    {
        $this->listingActive = $listingActive;
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
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return \get_object_vars($this);
    }
}
