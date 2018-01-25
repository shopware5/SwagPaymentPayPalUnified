<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Services;

use Shopware\Components\CacheManager;
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
     * @param CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param ClientService    $client
     * @param OAuthCredentials $credentials
     * @param int              $shopId
     *
     * @return Token
     */
    public function getToken(ClientService $client, OAuthCredentials $credentials, $shopId)
    {
        $token = $this->getTokenFromCache($shopId);
        if ($token === false || !$this->isTokenValid($token)) {
            $tokenResource = new TokenResource($client);

            $token = Token::fromArray($tokenResource->get($credentials));
            $this->setToken($token, $shopId);
        }

        return $token;
    }

    /**
     * @param int $shopId
     *
     * @return Token
     */
    private function getTokenFromCache($shopId)
    {
        return unserialize($this->cacheManager->getCoreCache()->load(self::CACHE_ID . $shopId));
    }

    /**
     * @param Token $token
     * @param int   $shopId
     */
    private function setToken(Token $token, $shopId)
    {
        $this->cacheManager->getCoreCache()->save(serialize($token), self::CACHE_ID . $shopId);
    }

    /**
     * @param Token $token
     *
     * @return bool
     */
    private function isTokenValid(Token $token)
    {
        $dateTimeNow = new \DateTime();
        $dateTimeExpire = $token->getExpireDateTime();
        //Decrease expire date by one hour just to make sure, we don't run into an unauthorized exception.
        $dateTimeExpire = $dateTimeExpire->sub(new \DateInterval('PT1H'));

        if ($dateTimeExpire < $dateTimeNow) {
            return false;
        }

        return true;
    }
}
