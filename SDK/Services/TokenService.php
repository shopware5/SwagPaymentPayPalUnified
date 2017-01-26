<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\SDK\Services;

use Shopware\Components\HttpClient\RequestException;
use Shopware\Components\Logger;
use SwagPaymentPayPalUnified\SDK\Resources\TokenResource;
use SwagPaymentPayPalUnified\SDK\Structs\OAuthCredentials;
use SwagPaymentPayPalUnified\SDK\Structs\Token;
use Shopware\Components\CacheManager;

class TokenService
{
    const CACHE_ID = 'paypal_unified_auth';

    /** @var CacheManager $cacheManager */
    private $cacheManager;

    /** @var Logger $logger */
    private $logger;

    /**
     * @param CacheManager $cacheManager
     * @param Logger $pluginLogger
     */
    public function __construct(CacheManager $cacheManager, Logger $pluginLogger)
    {
        $this->cacheManager = $cacheManager;
        $this->logger = $pluginLogger;
    }

    /**
     * @param ClientService $client
     * @param OAuthCredentials $credentials
     * @return Token
     */
    public function getToken(ClientService $client, OAuthCredentials $credentials)
    {
        $token = $this->getTokenFromCache();

        if ($token === false || !$this->isTokenValid($token)) {
            $tokenResource = new TokenResource($client);
            try {
                $token = Token::fromArray($tokenResource->get($credentials));
                $this->setToken($token);
            } catch (RequestException $ex) {
                $this->logger->log('PayPal Unified: API Authorization failed', [$ex->getMessage(), $ex->getBody()]);
            }
        }

        return $token;
    }

    /**
     * @return Token
     */
    private function getTokenFromCache()
    {
        return unserialize($this->cacheManager->getCoreCache()->load(self::CACHE_ID));
    }

    /**
     * @param Token $token
     */
    private function setToken(Token $token)
    {
        $this->cacheManager->getCoreCache()->save(serialize($token), self::CACHE_ID);
    }

    /**
     * @param Token $token
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
