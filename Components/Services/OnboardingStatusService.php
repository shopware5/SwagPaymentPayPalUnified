<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\MerchantIntegrationsResource;

class OnboardingStatusService
{
    const CAPABILITY_PAY_WITH_PAYPAL = 'PAY_WITH_PAYPAL';

    const PARTNER_ID = 'DYKPBPEAW5JNA';
    const SANDBOX_PARTNER_ID = '45KXQA7PULGAG';

    const STATUS_ACTIVE = 'ACTIVE';

    const CAPABILITIES_LIFETIME = 'PT15M';

    /**
     * @var MerchantIntegrationsResource
     */
    private $integrationsResource;

    /**
     * @var array<mixed>
     */
    private $lastResponseCapabilities;

    /**
     * @var DateTimeInterface
     */
    private $lastResponseCapabilitiesIsValidTo;

    public function __construct(
        MerchantIntegrationsResource $integrationsResource
    ) {
        $this->integrationsResource = $integrationsResource;
    }

    /**
     * @param string $payerId
     * @param int    $shopId
     * @param bool   $sandbox
     * @param string $targetCapability
     *
     * @throws RequestException
     *
     * @return bool
     */
    public function isCapable($payerId, $shopId, $sandbox, $targetCapability = self::CAPABILITY_PAY_WITH_PAYPAL)
    {
        $partnerId = self::PARTNER_ID;
        if ($sandbox) {
            $partnerId = self::SANDBOX_PARTNER_ID;
        }

        if ($this->lastResponseCapabilities === null || new DateTime() > $this->lastResponseCapabilitiesIsValidTo) {
            $response = $this->integrationsResource->getMerchantIntegrations($partnerId, $shopId, $payerId);

            if (!\is_array($response)) {
                return false;
            }

            $capabilities = $response['capabilities'];
            if (!\is_array($capabilities)) {
                return false;
            }

            $this->lastResponseCapabilities = $capabilities;
        }

        $this->lastResponseCapabilitiesIsValidTo = (new DateTime())->add(new DateInterval(self::CAPABILITIES_LIFETIME));

        foreach ($this->lastResponseCapabilities as $capability) {
            if (\is_array($capability) && \array_key_exists('name', $capability) && $capability['name'] === $targetCapability) {
                return $capability['status'] && $capability['status'] === self::STATUS_ACTIVE;
            }
        }

        return false;
    }
}
