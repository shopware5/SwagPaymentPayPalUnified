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
 * @ORM\Table(name="swag_payment_paypal_unified_settings_installments")
 */
class Installments extends ModelEntity
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
     * @ORM\Column(name="advertise_installments", type="boolean", nullable=false)
     */
    private $advertiseInstallments;

    /**
     * @var bool
     *
     * @ORM\Column(name="show_pay_later_paypal", type="boolean", nullable=false)
     */
    private $showPayLaterPaypal;

    /**
     * @var bool
     *
     * @ORM\Column(name="show_pay_later_express", type="boolean", nullable=false)
     */
    private $showPayLaterExpress;

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
    public function getAdvertiseInstallments()
    {
        return $this->advertiseInstallments;
    }

    /**
     * @param bool $advertiseInstallments
     *
     * @return void
     */
    public function setAdvertiseInstallments($advertiseInstallments)
    {
        $this->advertiseInstallments = $advertiseInstallments;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return \get_object_vars($this);
    }

    /**
     * @return bool
     */
    public function getShowPayLaterPaypal()
    {
        return $this->showPayLaterPaypal;
    }

    /**
     * @param bool $showPayLaterPaypal
     *
     * @return void
     */
    public function setShowPayLaterPaypal($showPayLaterPaypal)
    {
        $this->showPayLaterPaypal = $showPayLaterPaypal;
    }

    /**
     * @return bool
     */
    public function getShowPayLaterExpress()
    {
        return $this->showPayLaterExpress;
    }

    /**
     * @param bool $showPayLaterExpress
     *
     * @return void
     */
    public function setShowPayLaterExpress($showPayLaterExpress)
    {
        $this->showPayLaterExpress = $showPayLaterExpress;
    }
}
