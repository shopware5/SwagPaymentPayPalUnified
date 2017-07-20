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

namespace SwagPaymentPayPalUnified\Models\Settings;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="swag_payment_paypal_unified_settings_installments")
 */
class Installments extends ModelEntity
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
     * @var int
     * @ORM\Column(name="presentment_detail", type="integer")
     */
    private $presentmentTypeDetail;

    /**
     * @var int
     * @ORM\Column(name="presentment_cart", type="integer")
     */
    private $presentmentTypeCart;

    /**
     * @var bool
     * @ORM\Column(name="show_logo", type="boolean", nullable=false)
     */
    private $showLogo;

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
     * @return int
     */
    public function getPresentmentTypeDetail()
    {
        return $this->presentmentTypeDetail;
    }

    /**
     * @param int $presentmentTypeDetail
     */
    public function setPresentmentTypeDetail($presentmentTypeDetail)
    {
        $this->presentmentTypeDetail = $presentmentTypeDetail;
    }

    /**
     * @return int
     */
    public function getPresentmentTypeCart()
    {
        return $this->presentmentTypeCart;
    }

    /**
     * @param int $presentmentTypeCart
     */
    public function setPresentmentTypeCart($presentmentTypeCart)
    {
        $this->presentmentTypeCart = $presentmentTypeCart;
    }

    /**
     * @return bool
     */
    public function getShowLogo()
    {
        return $this->showLogo;
    }

    /**
     * @param bool $showLogo
     */
    public function setShowLogo($showLogo)
    {
        $this->showLogo = $showLogo;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
