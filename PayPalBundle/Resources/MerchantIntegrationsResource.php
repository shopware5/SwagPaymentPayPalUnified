<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Resources;

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\RequestUri;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use UnexpectedValueException;

class MerchantIntegrationsResource
{
    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function __construct(
        ClientService $clientService,
        SettingsServiceInterface $settingsService,
        LoggerServiceInterface $logger
    ) {
        $this->clientService = $clientService;
        $this->settingsService = $settingsService;
        $this->logger = $logger;
    }

    /**
     * @param string $partnerId
     * @param int    $shopId
     * @param string $payerId
     *
     * @throws RequestException
     *
     * @return array<string, mixed>
     */
    public function getMerchantIntegrations($partnerId, $shopId, $payerId)
    {
        $this->logger->debug(
            sprintf(
                '%s PARTNER ID:: %s, SHOP ID: %s, NONCE: %s, PAYER ID:',
                __METHOD__,
                $partnerId,
                $shopId,
                $payerId
            )
        );
        $settings = $this->settingsService->getSettings($shopId);

        if (!$settings instanceof General) {
            $this->logger->debug(sprintf('%s SETTINGS NOT FOUND', __METHOD__));

            throw new UnexpectedValueException(sprintf('Expected instance of "%s", got "%s".', General::class, $settings === null ? 'null' : \get_class($settings)));
        }

        $this->clientService->configure($settings->toArray());

        return $this->clientService->sendRequest(
            RequestType::GET,
            sprintf(RequestUri::MERCHANT_INTEGRATIONS_RESOURCE, $partnerId, $payerId)
        );
    }
}
