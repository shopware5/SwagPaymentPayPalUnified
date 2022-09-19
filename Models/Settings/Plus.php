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
 * @ORM\Table(name="swag_payment_paypal_unified_settings_plus")
 */
class Plus extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="shop_id", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var bool
     *
     * @ORM\Column(name="restyle", type="boolean", nullable=false)
     */
    private $restyle;

    /**
     * @var bool
     *
     * @ORM\Column(name="integrate_third_party_methods", type="boolean", nullable=false)
     */
    private $integrateThirdPartyMethods;

    /**
     * @var string|null
     *
     * @ORM\Column(name="payment_name", type="string", nullable=true)
     */
    private $paymentName;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_description", type="string")
     */
    private $paymentDescription;

    /**
     * @var bool
     *
     * @ORM\Column(name="ppcp_active", type="boolean")
     */
    private $ppcpActive;

    /**
     * @var bool
     *
     * @ORM\Column(name="sandbox_ppcp_active", type="boolean")
     */
    private $sandboxPpcpActive;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
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
     * @return bool
     */
    public function getRestyle()
    {
        return $this->restyle;
    }

    /**
     * @param bool $restyle
     *
     * @return void
     */
    public function setRestyle($restyle)
    {
        $this->restyle = $restyle;
    }

    /**
     * @return bool
     */
    public function getIntegrateThirdPartyMethods()
    {
        return $this->integrateThirdPartyMethods;
    }

    /**
     * @param bool $integrateThirdPartyMethods
     *
     * @return void
     */
    public function setIntegrateThirdPartyMethods($integrateThirdPartyMethods)
    {
        $this->integrateThirdPartyMethods = $integrateThirdPartyMethods;
    }

    /**
     * @return string|null
     */
    public function getPaymentName()
    {
        return $this->paymentName;
    }

    /**
     * @param string $paymentName
     *
     * @return void
     */
    public function setPaymentName($paymentName)
    {
        $this->paymentName = $paymentName;
    }

    /**
     * @return string
     */
    public function getPaymentDescription()
    {
        return $this->paymentDescription;
    }

    /**
     * @param string $paymentDescription
     *
     * @return void
     */
    public function setPaymentDescription($paymentDescription)
    {
        $this->paymentDescription = $paymentDescription;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return \get_object_vars($this);
    }

    /**
     * @return bool
     */
    public function isPpcpActive()
    {
        return $this->ppcpActive;
    }

    /**
     * @param bool $ppcpActive
     *
     * @return void
     */
    public function setPpcpActive($ppcpActive)
    {
        $this->ppcpActive = $ppcpActive;
    }

    /**
     * @return bool
     */
    public function isSandboxPpcpActive()
    {
        return $this->sandboxPpcpActive;
    }

    /**
     * @param bool $sandboxPpcpActive
     *
     * @return void
     */
    public function setSandboxPpcpActive($sandboxPpcpActive)
    {
        $this->sandboxPpcpActive = $sandboxPpcpActive;
    }
}
