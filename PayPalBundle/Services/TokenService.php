<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Services;

use DateInterval;
use DateTime;
use Shopware\Components\CacheManager;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\TokenResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\OAuthCredentials;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Token;

class TokenService
{
    const CACHE_ID = 'paypal_unified_auth_';

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function __construct(CacheManager $cacheManager, LoggerServiceInterface $logger)
    {
        $this->cacheManager = $cacheManager;
        $this->logger = $logger;
    }

    /**
     * @param int $shopId
     *
     * @return Token
     */
    public function getToken(ClientService $client, OAuthCredentials $credentials, $shopId)
    {
        $this->logger->debug(sprintf('%s START GET TOKEN WITH CREDENTIALS: %s AND SHOP ID: %s', __METHOD__, $credentials->toString(), $shopId));

        $token = $this->getTokenFromCache($shopId);
        if ($token === false || !$this->isTokenValid($token)) {
            $tokenResource = new TokenResource($client, $this->logger);

            $token = Token::fromArray($tokenResource->get($credentials));
            $this->setToken($token, $shopId);

            $this->logger->debug(sprintf('%s GENERATED NEW TOKEN FOR SHOP ID: %s', __METHOD__, $shopId));
        } else {
            $this->logger->debug(sprintf('%s GOT TOKEN FROM CACHE FOR SHOP ID: %s', __METHOD__, $shopId));
        }

        return $token;
    }

    /**
     * @param int $shopId
     *
     * @return void
     */
    public function invalidateCache($shopId)
    {
        $this->logger->debug(sprintf('%s INVALIDATING AUTHENTICATION CACHE FOR SHOP ID: %d', __METHOD__, $shopId));

        $this->cacheManager->getCoreCache()->remove(self::CACHE_ID . $shopId);
    }

    /**
     * @param int $shopId
     *
     * @return Token|false
     */
    private function getTokenFromCache($shopId)
    {
        $this->logger->debug(sprintf('%s START READ TOKEN FROM CACHE WITH SHOP ID: %s', __METHOD__, $shopId));

        $token = \unserialize($this->cacheManager->getCoreCache()->load(self::CACHE_ID . $shopId));

        if (!$token instanceof Token) {
            $this->logger->debug(sprintf('%s TOKEN CANNOT BE READ FROM CACHE WITH SHOP ID: %s', __METHOD__, $shopId));
        }

        return $token;
    }

    /**
     * @param int $shopId
     */
    private function setToken(Token $token, $shopId)
    {
        $this->logger->debug(sprintf('%s SET TOKEN WITH SHOP ID: %s TO CACHE', __METHOD__, $shopId));

        $this->cacheManager->getCoreCache()->save(\serialize($token), self::CACHE_ID . $shopId);
    }

    /**
     * @return bool
     */
    private function isTokenValid(Token $token)
    {
        $dateTimeNow = new DateTime();
        $dateTimeExpire = $token->getExpireDateTime();
        // Decrease expire date by one minute just to make sure, we don't run into an unauthorized exception.
        $dateTimeExpire = $dateTimeExpire->sub(new DateInterval('PT1M'));

        if ($dateTimeExpire < $dateTimeNow) {
            $this->logger->debug(sprintf('%s TOKEN IS NO LONGER VALID', __METHOD__), [
                'expire_date' => $dateTimeExpire->format('Y-m-d H:i:s'),
                'now_date' => $dateTimeNow->format('Y-m-d H:i:s'),
            ]);

            return false;
        }

        $this->logger->debug(sprintf('%s TOKEN IS STILL VALID', __METHOD__), [
            'expire_date' => $dateTimeExpire->format('Y-m-d H:i:s'),
            'now_date' => $dateTimeNow->format('Y-m-d H:i:s'),
        ]);

        return true;
    }
}
