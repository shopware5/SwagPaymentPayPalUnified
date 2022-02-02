<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Services;

use Exception;
use RuntimeException;
use Shopware\Components\HttpClient\GuzzleFactory;
use Shopware\Components\HttpClient\GuzzleHttpClient as GuzzleClient;
use Shopware\Components\HttpClient\RequestException;
use Shopware\Components\HttpClient\Response;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\PayPalBundle\BaseURL;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\OAuthCredentials;

class ClientService
{
    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var TokenService
     */
    private $tokenService;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var int
     */
    private $shopId;

    public function __construct(
        SettingsServiceInterface $settingsService,
        TokenService $tokenService,
        LoggerServiceInterface $logger,
        GuzzleFactory $factory,
        DependencyProvider $dependencyProvider
    ) {
        $this->settingsService = $settingsService;
        $this->tokenService = $tokenService;
        $this->logger = $logger;
        $this->client = new GuzzleClient($factory);
        $this->dependencyProvider = $dependencyProvider;

        //Backend does not have any active shop. In order to authenticate there, please use
        //the "configure()"-function instead.
        if (!$this->settingsService->hasSettings() || !$this->settingsService->get(SettingsServiceInterface::SETTING_ACTIVE)) {
            return;
        }

        $environment = (bool) $this->settingsService->get(SettingsServiceInterface::SETTING_SANDBOX);
        $environment === true ? $this->baseUrl = BaseURL::SANDBOX : $this->baseUrl = BaseURL::LIVE;

        //Set Partner-Attribution-Id
        $this->setPartnerAttributionId(PartnerAttributionId::PAYPAL_CLASSIC); //Default
    }

    /**
     * @param array<string,mixed> $settings
     *
     * @throws RequestException
     */
    public function configure(array $settings)
    {
        $this->logger->debug(sprintf('%s CONFIGURE', __METHOD__), $settings);

        $this->shopId = (int) $settings['shopId'];
        $sandbox = $settings['sandbox'];

        if ($sandbox) {
            $this->baseUrl = BaseURL::SANDBOX;
        } else {
            $this->baseUrl = BaseURL::LIVE;
        }

        //Create authentication
        $credentials = new OAuthCredentials();

        $credentials->setRestId($sandbox ? $settings['sandboxClientId'] : $settings['clientId']);
        $credentials->setRestSecret($sandbox ? $settings['sandboxClientSecret'] : $settings['clientSecret']);

        $this->createAuthentication($credentials);
    }

    /**
     * Sends a request and returns the full response.
     * The type can be obtained from RequestType.php
     *
     * @param string            $type
     * @param string            $resourceUri
     * @param array<mixed>|null $data
     * @param bool              $jsonPayload true if the given data should be JSON-encoded
     *
     * @throws RequestException
     *
     * @return array<mixed>
     */
    public function sendRequest($type, $resourceUri, $data = [], $jsonPayload = true)
    {
        $httpClient = $this->getClient();
        $resourceUri = $this->baseUrl . $resourceUri;

        if ($jsonPayload) {
            $data = json_encode($data);
            if (!\is_string($data)) {
                $data = null;
            }
            $this->setHeader('content-type', 'application/json');
        } else {
            unset($this->headers['content-type']);
        }

        $this->logger->notify('Sending request [' . $type . '] to ' . $resourceUri, ['payload' => $data]);

        switch ($type) {
            case RequestType::POST:
                $response = $httpClient->post($resourceUri, $this->headers, $data);
                break;

            case RequestType::GET:
                $response = $httpClient->get($resourceUri, $this->headers);
                break;

            case RequestType::PATCH:
                $response = $httpClient->patch($resourceUri, $this->headers, $data);
                break;

            case RequestType::PUT:
                $response = $httpClient->put($resourceUri, $this->headers, $data);
                break;

            case RequestType::HEAD:
                $response = $httpClient->head($resourceUri, $this->headers);
                break;

            case RequestType::DELETE:
                $response = $httpClient->delete($resourceUri, $this->headers);
                break;

            default:
                throw new RuntimeException('An unsupported request type was provided. The type was: ' . $type);
        }

        $this->logger->notify(
            'Received data from ' . $resourceUri,
            [
                'payload' => $response->getBody(),
                'debug-id' => $response->getHeader('Paypal-Debug-Id') ?: '',
            ]
        );

        return json_decode($response->getBody(), true);
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
     * @param string $partnerId
     */
    public function setPartnerAttributionId($partnerId)
    {
        $this->setHeader('PayPal-Partner-Attribution-Id', $partnerId);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getHeader($key)
    {
        return $this->headers[$key];
    }

    /**
     * @throws RequestException
     *
     * @return GuzzleClient
     */
    public function getClient()
    {
        if ($this->getHeader('Authorization')) {
            return $this->client;
        }

        $sandbox = (bool) $this->settingsService->get(SettingsServiceInterface::SETTING_SANDBOX);

        if ($sandbox) {
            $this->baseUrl = BaseURL::SANDBOX;
        } else {
            $this->baseUrl = BaseURL::LIVE;
        }

        //Create authentication
        $credentials = new OAuthCredentials();

        $credentials->setRestId($this->settingsService->get(
            $sandbox ? SettingsServiceInterface::SETTING_SANDBOX_CLIENT_ID : SettingsServiceInterface::SETTING_CLIENT_ID
        ));
        $credentials->setRestSecret($this->settingsService->get(
            $sandbox ? SettingsServiceInterface::SETTING_SANDBOX_CLIENT_SECRET : SettingsServiceInterface::SETTING_CLIENT_SECRET
        ));

        $this->createAuthentication($credentials);

        return $this->client;
    }

    /**
     * Creates the authentication header for the PayPal API.
     * If there is no cached token yet, it will be generated on the fly.
     *
     * @throws RequestException
     */
    private function createAuthentication(OAuthCredentials $credentials)
    {
        $shop = $this->dependencyProvider->getShop();
        $shopId = $this->shopId;

        if ($shop instanceof Shop) {
            $shopId = $shop->getId();
        }

        try {
            $this->logger->debug(sprintf('%s CREATE AUTHENTICATION, WITH CREDENTIALS: %s', __METHOD__, $credentials->toString()));

            $token = $this->tokenService->getToken($this, $credentials, $shopId);
            $this->setHeader('Authorization', $token->getTokenType() . ' ' . $token->getAccessToken());

            $this->logger->debug(sprintf('%s %s', __METHOD__, 'AUTHENTICATION SUCCESSFUL CREATED'));
        } catch (RequestException $requestException) {
            $this->logger->error('Could not create authentication - request exception', [
                'payload' => $requestException->getBody(),
                'message' => $requestException->getMessage(),
            ]);

            throw $requestException;
        } catch (Exception $e) {
            $this->logger->error('Could not create authentication - unknown exception', [
                'message' => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString(),
            ]);
        }
    }
}
