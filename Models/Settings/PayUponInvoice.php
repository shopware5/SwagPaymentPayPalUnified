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
 * @ORM\Table(name="swag_payment_paypal_unified_settings_pay_upon_invoice")
 */
class PayUponInvoice extends ModelEntity
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
     * @var string|null
     * @ORM\Column(name="customer_service_instructions", type="text", length=65535, nullable=true)
     */
    private $customerServiceInstructions;

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
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
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
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return array{id: int, shopId: int, isOnboardingCompleted: bool, isSandboxOnboardingCompleted: bool, active: bool}
     * @phpstan-return array<string, mixed>
     */
    public function toArray()
    {
        return \get_object_vars($this);
    }

    /**
     * @return string|null
     */
    public function getCustomerServiceInstructions()
    {
        return $this->customerServiceInstructions;
    }

    /**
     * @param string|null $customerServiceInstructions
     *
     * @return void
     */
    public function setCustomerServiceInstructions($customerServiceInstructions)
    {
        $this->customerServiceInstructions = $customerServiceInstructions;
    }
}
