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

use Shopware\Components\HttpClient\GuzzleFactory;
use Shopware\Components\HttpClient\GuzzleHttpClient as GuzzleClient;
use Shopware\Components\HttpClient\RequestException;
use Shopware\Components\Logger;
use SwagPaymentPayPalUnified\SDK\BaseURL;
use SwagPaymentPayPalUnified\SDK\RequestType;
use SwagPaymentPayPalUnified\SDK\Structs\OAuthCredentials;
use SwagPaymentPayPalUnified\SDK\Structs\Token;

class ClientService
{
    /** @var GuzzleClient $client */
    private $client;

    /** @var array $headers */
    protected $headers;

    /** @var string $baseUrl */
    protected $baseUrl;

    /** @var TokenService $tokenService */
    protected $tokenService;

    /** @var Logger $logger */
    protected $logger;

    /**
     * @param \Shopware_Components_Config $config
     * @param TokenService $tokenService
     * @param Logger $logger
     * @param GuzzleFactory $factory
     * @param PartnerAttributionService $partnerAttributionService
     */
    public function __construct(
        \Shopware_Components_Config $config,
        TokenService $tokenService,
        Logger $logger,
        GuzzleFactory $factory,
        PartnerAttributionService $partnerAttributionService
    ) {
        $this->tokenService = $tokenService;
        $this->logger = $logger;

        $environment = (bool) $config->getByNamespace('SwagPaymentPayPalUnified', 'enableSandbox');
        $environment === true ? $this->baseUrl = BaseURL::SANDBOX : $this->baseUrl = BaseURL::LIVE;

        $this->client = new GuzzleClient($factory);

        //Set Partner-Attribution-Id
        $this->setPartnerAttributionId($partnerAttributionService->getPartnerAttributionId());

        //Create authentication
        $restId = $config->getByNamespace('SwagPaymentPayPalUnified', 'restId');
        $restSecret = $config->getByNamespace('SwagPaymentPayPalUnified', 'restSecret');
        $credentials = new OAuthCredentials();
        $credentials->setRestId($restId);
        $credentials->setRestSecret($restSecret);
        $this->createAuthentication($credentials);
    }

    /**
     * Sends a request and returns the response.
     * The type can be obtained from RequestType.php
     *
     * @param string $type
     * @param string $resourceUri
     * @param array|string $data
     * @param bool $jsonPayload
     * @return array
     * @throws \Exception
     */
    public function sendRequest($type, $resourceUri, array $data = [], $jsonPayload = true)
    {
        $resourceUri = $this->baseUrl . $resourceUri;

        if ($jsonPayload) {
            $data = json_encode($data);
            $this->setHeader('content-type', 'application/json');
        } else {
            unset($this->headers['content-type']);
        }

        switch ($type) {
            case RequestType::POST:
                $response = $this->client->post($resourceUri, $this->headers, $data)->getBody();
                break;

            case RequestType::GET:
                $response = $this->client->get($resourceUri, $this->headers)->getBody();
                break;

            case RequestType::PATCH:
                $response = $this->client->patch($resourceUri, $this->headers, $data)->getBody();
                break;

            case RequestType::PUT:
                $response = $this->client->put($resourceUri, $this->headers, $data)->getBody();
                break;

            case RequestType::HEAD:
                $response = $this->client->head($resourceUri, $this->headers)->getBody();
                break;

            case RequestType::DELETE:
                $response = $this->client->delete($resourceUri, $this->headers)->getBody();
                break;

            default:
                throw new \RuntimeException('An unsupported request type was provided. The type was: ' . $type);
        }

        return json_decode($response, true);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getHeader($key)
    {
        return $this->headers[$key];
    }

    /**
     * Creates the authentication header for the PayPal API.
     * If there is no cached token yet, it will be generated on the fly.
     *
     * @param OAuthCredentials $credentials
     */
    private function createAuthentication(OAuthCredentials $credentials)
    {
        try {
            /** @var Token $cachedToken */
            $token = $this->tokenService->getToken($this, $credentials);
            $this->setHeader('Authorization', $token->getTokenType() . ' ' . $token->getAccessToken());
        } catch (RequestException $requestException) {
            $this->logger->error('PayPal: Could not create authentication - request exception', [
                $requestException->getBody(),
                $requestException->getMessage()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('PayPal: Could not create authentication - unknown exception', [
                $e->getMessage()
            ]);
        }
    }

    /**
     * @param string $partnerId
     */
    private function setPartnerAttributionId($partnerId)
    {
        $this->setHeader('PayPal-Partner-Attribution-Id', $partnerId);
    }
}
