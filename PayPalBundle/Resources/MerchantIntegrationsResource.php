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
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\RequestUri;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;

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

    public function __construct(ClientService $clientService, SettingsServiceInterface $settingsService)
    {
        $this->clientService = $clientService;
        $this->settingsService = $settingsService;
    }

    /**
     * @param string $partnerId
     * @param int    $shopId
     *
     * @throws RequestException
     *
     * @return array<string, mixed>
     */
    public function getMerchantIntegrations($partnerId, $shopId)
    {
        $settings = $this->settingsService->getSettings($shopId);

        if (!$settings instanceof General) {
            throw new \UnexpectedValueException(sprintf('Expected instance of "%s", got "%s".', General::class, $settings === null ? 'null' : \get_class($settings)));
        }

        $this->clientService->configure($settings->toArray());

        $userinfo = $this->clientService->sendRequestFull(
            RequestType::GET,
            sprintf('%s?%s', RequestUri::USER_INFO_RESOURCE, 'schema=paypalv1.1')
        );

        $body = \json_decode($userinfo->getBody(), true);

        $payerId = $body['payer_id'] ?: $userinfo->getHeader('Caller_acct_num');

        if (!$payerId) {
            return [];
        }

        return $this->clientService->sendRequest(
            RequestType::GET,
            sprintf(RequestUri::MERCHANT_INTEGRATIONS_RESOURCE, $partnerId, $payerId)
        );
    }
}
