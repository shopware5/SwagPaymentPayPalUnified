<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Resource;

use DateTime;
use Shopware\Components\CacheManager;
use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\ClientToken;
use SwagPaymentPayPalUnified\PayPalBundle\V2\RequestUriV2;

class ClientTokenResource
{
    const CACHE_KEY_TEMPLATE = 'paypal_unified_client_token_%s';

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    public function __construct(ClientService $clientService, CacheManager $cacheManager)
    {
        $this->clientService = $clientService;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param int $shopId
     *
     * @return ClientToken
     */
    public function generateToken($shopId)
    {
        $clientToken = $this->loadFromCache($shopId);

        if ($clientToken !== false && !$this->isClientTokenExpired($clientToken)) {
            return $clientToken;
        }

        $response = $this->clientService->sendRequest(
            RequestType::POST,
            RequestUriV2::CLIENT_TOKEN_RESOURCE,
            null
        );

        $clientToken = (new ClientToken())->assign($response);

        $this->saveToCache($clientToken, $shopId);

        return $clientToken;
    }

    /**
     * @return bool
     */
    private function isClientTokenExpired(ClientToken $token)
    {
        $dateTimeNow = new DateTime();
        $dateTimeExpire = $token->getExpires();

        if ($dateTimeNow < $dateTimeExpire) {
            return false;
        }

        return true;
    }

    /**
     * @param int $shopId
     *
     * @return void
     */
    private function saveToCache(ClientToken $clientToken, $shopId)
    {
        $this->cacheManager->getCoreCache()->save(
            serialize($clientToken),
            sprintf(self::CACHE_KEY_TEMPLATE, $shopId)
        );
    }

    /**
     * @param int $shopId
     *
     * @return ClientToken|false
     */
    private function loadFromCache($shopId)
    {
        return unserialize(
            $this->cacheManager->getCoreCache()->load(
                sprintf(self::CACHE_KEY_TEMPLATE, $shopId)
            )
        );
    }
}
