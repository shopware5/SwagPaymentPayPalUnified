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
    const EVENT_PRODUCT_SUBSCRIPTION_STATUS_UPDATE = 'SwagPaymentPayPalUnified_ProductSubscriptionStatusUpdate';
    const EVENT_CAPABILITY_STATUS_UPDATE = 'SwagPaymentPayPalUnified_CapabilityStatusUpdate';

    const CAPABILITY_PAY_WITH_PAYPAL = 'PAY_WITH_PAYPAL';
    const CAPABILITY_PAY_UPON_INVOICE = 'PAY_UPON_INVOICE';
    const CAPABILITY_ADVANCED_CREDIT_DEBIT_CARD = 'CUSTOM_CARD_PROCESSING';

    const PRODUCT_PPCP = 'PPCP_STANDARD';

    const PARTNER_ID = 'DYKPBPEAW5JNA';
    const SANDBOX_PARTNER_ID = '45KXQA7PULGAG';

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_SUBSCRIBED = 'SUBSCRIBED';

    const CACHE_LIFETIME = 'PT15M';

    /**
     * @var array<mixed>
     */
    private $lastResponseCapabilities;

    /**
     * @var array<mixed>
     */
    private $lastResponseProducts;

    /**
     * @var DateTimeInterface
     */
    private $expiry;

    /**
     * @var MerchantIntegrationsResource
     */
    private $integrationsResource;

    public function __construct(MerchantIntegrationsResource $integrationsResource)
    {
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

        if ($this->lastResponseCapabilities === null || new DateTime() > $this->expiry) {
            $response = $this->integrationsResource->getMerchantIntegrations($partnerId, $shopId, $payerId);

            if (!\is_array($response)) {
                return false;
            }

            $capabilities = $response['capabilities'];
            if (!\is_array($capabilities)) {
                return false;
            }

            $this->lastResponseCapabilities = $capabilities;
            $this->expiry = (new DateTime())->add(new DateInterval(self::CACHE_LIFETIME));
        }

        foreach ($this->lastResponseCapabilities as $capability) {
            if (\is_array($capability) && \array_key_exists('name', $capability) && $capability['name'] === $targetCapability) {
                return $capability['status'] && $capability['status'] === self::STATUS_ACTIVE;
            }
        }

        return false;
    }

    /**
     * @param string $payerId
     * @param int    $shopId
     * @param bool   $sandbox
     * @param string $targetProduct
     *
     * @throws RequestException
     *
     * @return bool
     */
    public function isSubscribed($payerId, $shopId, $sandbox, $targetProduct = self::PRODUCT_PPCP)
    {
        $partnerId = self::PARTNER_ID;
        if ($sandbox) {
            $partnerId = self::SANDBOX_PARTNER_ID;
        }

        if ($this->lastResponseProducts === null || new DateTime() > $this->expiry) {
            $response = $this->integrationsResource->getMerchantIntegrations($partnerId, $shopId, $payerId);

            if (!\is_array($response)) {
                return false;
            }

            $products = $response['products'];
            if (!\is_array($products)) {
                return false;
            }

            $this->lastResponseProducts = $products;
            $this->expiry = (new DateTime())->add(new DateInterval(self::CACHE_LIFETIME));
        }

        foreach ($this->lastResponseProducts as $product) {
            if (\is_array($product) && \array_key_exists('name', $product) && $product['name'] === $targetProduct) {
                return $product['vetting_status'] && $product['vetting_status'] === self::STATUS_SUBSCRIBED;
            }
        }

        return false;
    }
}
