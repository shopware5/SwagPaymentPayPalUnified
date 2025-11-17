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
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
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

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function __construct(ClientService $clientService, CacheManager $cacheManager, LoggerServiceInterface $logger)
    {
        $this->clientService = $clientService;
        $this->cacheManager = $cacheManager;
        $this->logger = $logger;
    }

    /**
     * @param int $shopId
     *
     * @return ClientToken
     */
    public function generateToken($shopId)
    {
        $this->logger->debug(\sprintf('%s GENERATE CLIENT TOKEN START', __METHOD__));
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
            $this->logger->debug(\sprintf('%s CLIENT TOKEN IS VALID', __METHOD__), [
                'expire_date' => $dateTimeExpire->format('Y-m-d H:i:s'),
                'now_date' => $dateTimeNow->format('Y-m-d H:i:s'),
            ]);

            return false;
        }

        $this->logger->debug(\sprintf('%s CLIENT TOKEN IS NO LONGER VALID', __METHOD__), [
            'expire_date' => $dateTimeExpire->format('Y-m-d H:i:s'),
            'now_date' => $dateTimeNow->format('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /**
     * @param int $shopId
     *
     * @return void
     */
    private function saveToCache(ClientToken $clientToken, $shopId)
    {
        $serializedToken = serialize($clientToken);

        $this->logger->debug(\sprintf('%s SAVE CLIENT TOKEN TO CACHE', __METHOD__), [
            'client_token' => $serializedToken,
        ]);

        $this->cacheManager->getCoreCache()->save(
            $serializedToken,
            \sprintf(self::CACHE_KEY_TEMPLATE, $shopId)
        );
    }

    /**
     * @param int $shopId
     *
     * @return ClientToken|false
     */
    private function loadFromCache($shopId)
    {
        $this->logger->debug(\sprintf('%s READ CLIENT TOKEN FROM CACHE START', __METHOD__));

        $clientToken = unserialize(
            $this->cacheManager->getCoreCache()->load(
                \sprintf(self::CACHE_KEY_TEMPLATE, $shopId)
            )
        );

        if (!$clientToken instanceof ClientToken) {
            $this->logger->debug(\sprintf('%s CLIENT TOKEN FROM CACHE IS EMPTY', __METHOD__));
        }

        return $clientToken;
    }
}
