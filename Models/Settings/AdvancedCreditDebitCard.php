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
 * @ORM\Table(name="swag_payment_paypal_unified_settings_advanced_credit_debit_card")
 */
class AdvancedCreditDebitCard extends ModelEntity
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
     * @var int
     * @ORM\Column(name="shop_id", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * @var bool
     * @ORM\Column(name="onboarding_completed", type="boolean", nullable=false)
     */
    private $onboardingCompleted;

    /**
     * @var bool
     * @ORM\Column(name="sandbox_onboarding_completed", type="boolean", nullable=false)
     */
    private $sandboxOnboardingCompleted;

    /**
     * @var bool
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

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
    public function isOnboardingCompleted()
    {
        return $this->onboardingCompleted;
    }

    /**
     * @param bool $onboardingCompleted
     *
     * @return void
     */
    public function setOnboardingCompleted($onboardingCompleted)
    {
        $this->onboardingCompleted = $onboardingCompleted;
    }

    /**
     * @return bool
     */
    public function isSandboxOnboardingCompleted()
    {
        return $this->sandboxOnboardingCompleted;
    }

    /**
     * @param bool $sandboxOnboardingCompleted
     *
     * @return void
     */
    public function setSandboxOnboardingCompleted($sandboxOnboardingCompleted)
    {
        $this->sandboxOnboardingCompleted = $sandboxOnboardingCompleted;
    }

    /**
     * @return bool
     */
    public function isActive()
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
     * @return array{id: int, shopId: int, isOnboardingCompleted: bool, isSandboxOnboardingCompleted: bool, active: bool}
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return \get_object_vars($this);
    }
}
