<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Doctrine\ORM\EntityManagerInterface;
use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Models\Settings\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\MerchantIntegrationsResource;

class OnboardingStatusService
{
    const CAPABILITY_PAY_WITH_PAYPAL = 'PAY_WITH_PAYPAL';
    const CAPABILITY_PAY_UPON_INVOICE = 'PAY_UPON_INVOICE';

    const STATUS_ACTIVE = 'ACTIVE';

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MerchantIntegrationsResource
     */
    private $integrationsResource;

    public function __construct(
        SettingsServiceInterface $settingsService,
        EntityManagerInterface $entityManager,
        MerchantIntegrationsResource $integrationsResource
    ) {
        $this->settingsService = $settingsService;
        $this->entityManager = $entityManager;
        $this->integrationsResource = $integrationsResource;
    }

    /**
     * @param string $partnerId
     * @param int    $shopId
     * @param string $targetCapability
     *
     * @throws RequestException
     *
     * @return bool
     */
    public function isCapable($partnerId, $shopId, $targetCapability = self::CAPABILITY_PAY_WITH_PAYPAL)
    {
        // TODO: (PT-12582) (Optional improvment) Put this response in a cache (like it's done in the TokenService) to enable consumers to just query this method multiple times for all capabilities.
        $response = $this->integrationsResource->getMerchantIntegrations($partnerId, $shopId);

        if (!\is_array($response)) {
            return false;
        }

        $capabilities = $response['capabilities'];

        if (!\is_array($capabilities)) {
            return false;
        }

        foreach ($capabilities as $capability) {
            if (\is_array($capability) && \array_key_exists('name', $capability) && $capability['name'] === $targetCapability) {
                return $capability['status'] && $capability['status'] === self::STATUS_ACTIVE;
            }
        }

        return false;
    }

    /**
     * @param int  $shopId
     * @param bool $sandbox
     * @param bool $onboardingCompleted
     *
     * @return void
     */
    public function updatePayUponInvoiceOnboardingStatus($shopId, $sandbox, $onboardingCompleted)
    {
        /** @var PayUponInvoice $settings */
        $settings = $this->settingsService->getSettings($shopId, SettingsTable::PAY_UPON_INVOICE);

        if (!$settings instanceof PayUponInvoice) {
            $settings = (new PayUponInvoice())->fromArray([
                'shopId' => $shopId,
                'onboardingCompleted' => false,
                'sandboxOnboardingCompleted' => false,
                'active' => false,
            ]);
        }

        if ($sandbox) {
            $settings->setSandboxOnboardingCompleted($onboardingCompleted);
        } else {
            $settings->setOnboardingCompleted($onboardingCompleted);
        }

        $this->entityManager->persist($settings);
        $this->entityManager->flush();
    }
}
