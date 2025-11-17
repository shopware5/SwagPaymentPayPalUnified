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
use SwagPaymentPayPalUnified\Components\Services\Onboarding\IsCapableResult;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
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

    /**
     * @var LoggerServiceInterface
     */
    private $loggerService;

    /**
     * @var bool|null
     */
    private $lastResponsePaymentsReceivable;

    /**
     * @var bool|null
     */
    private $lastResponsePrimaryEmailConfirmed;

    public function __construct(
        MerchantIntegrationsResource $integrationsResource,
        LoggerServiceInterface $loggerService
    ) {
        $this->integrationsResource = $integrationsResource;
        $this->loggerService = $loggerService;
    }

    /**
     * @param string $payerId
     * @param int    $shopId
     * @param bool   $sandbox
     * @param string $targetCapability
     *
     * @return IsCapableResult
     */
    public function getIsCapableResult(
        $payerId,
        $shopId,
        $sandbox,
        $targetCapability = self::CAPABILITY_PAY_WITH_PAYPAL
    ) {
        $partnerId = self::PARTNER_ID;

        if ($sandbox) {
            $partnerId = self::SANDBOX_PARTNER_ID;
        }

        if ($this->lastResponseCapabilities === null || new DateTime() > $this->expiry) {
            $response = $this->integrationsResource->getMerchantIntegrations($partnerId, $shopId, $payerId);

            if (!\is_array($response)) {
                // Coarse logging is enough here, as the request parameters are logged inside the resource class
                $this->loggerService->debug(\sprintf('%s MERCHANT INTEGRATIONS CALL UNSUCCESSFUL', __METHOD__));

                return new IsCapableResult(false);
            }

            $this->loggerService->debug(\sprintf('%s MERCHANT INTEGRATIONS: %s', __METHOD__, \json_encode($response)));

            $this->lastResponsePaymentsReceivable = $this->getBoolValueFromResponseArray(IsCapableResult::PAYMENTS_RECEIVABLE, $response);
            $this->lastResponsePrimaryEmailConfirmed = $this->getBoolValueFromResponseArray(IsCapableResult::PRIMARY_EMAIL_CONFIRMED, $response);

            if (!\array_key_exists('capabilities', $response) || !\is_array($response['capabilities'])) {
                return new IsCapableResult(false, null, $this->lastResponsePaymentsReceivable, $this->lastResponsePrimaryEmailConfirmed);
            }

            $capabilities = $response['capabilities'];

            $this->lastResponseCapabilities = $capabilities;
            $this->expiry = (new DateTime())->add(new DateInterval(self::CACHE_LIFETIME));
        }

        foreach ($this->lastResponseCapabilities as $capability) {
            if (\is_array($capability) && \array_key_exists('name', $capability) && $capability['name'] === $targetCapability) {
                $isCapable = $capability['status'] && $capability['status'] === self::STATUS_ACTIVE;

                return new IsCapableResult(
                    $isCapable,
                    \array_key_exists('limits', $capability) ? $capability['limits'] : null,
                    $this->lastResponsePaymentsReceivable,
                    $this->lastResponsePrimaryEmailConfirmed
                );
            }
        }

        return new IsCapableResult(false);
    }

    /**
     * @param string $payerId
     * @param int    $shopId
     * @param bool   $sandbox
     * @param string $targetProduct
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
                // Coarse logging is enough here, as the request parameters are logged inside the resource class
                $this->loggerService->debug(\sprintf('%s MERCHANT INTEGRATIONS CALL UNSUCCESSFUL', __METHOD__));

                return false;
            }

            $this->loggerService->debug(\sprintf('%s MERCHANT INTEGRATIONS: %s', __METHOD__, \json_encode($response)));

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

    /**
     * @param string              $arrayKey
     * @param array<string,mixed> $response
     * @param bool                $default
     *
     * @return bool
     */
    private function getBoolValueFromResponseArray($arrayKey, array $response, $default = false)
    {
        if (\array_key_exists($arrayKey, $response)) {
            return $response[$arrayKey];
        }

        return $default;
    }
}
