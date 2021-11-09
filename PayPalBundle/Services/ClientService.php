<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Services;

use Shopware\Components\HttpClient\GuzzleFactory;
use Shopware\Components\HttpClient\GuzzleHttpClient as GuzzleClient;
use Shopware\Components\HttpClient\RequestException;
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
     * @var int
     */
    private $shopId;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

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

        $shop = $dependencyProvider->getShop();

        if (!$shop instanceof Shop) {
            throw new \UnexpectedValueException(sprintf('Tried to access %s, but it\'s not set in the DIC.', Shop::class));
        }

        //Backend does not have any active shop. In order to authenticate there, please use
        //the "configure()"-function instead.
        if (!$this->settingsService->hasSettings() || !$this->settingsService->get('active')) {
            return;
        }

        $this->shopId = $shop->getId();

        $environment = (bool) $this->settingsService->get('sandbox');
        $environment === true ? $this->baseUrl = BaseURL::SANDBOX : $this->baseUrl = BaseURL::LIVE;

        //Set Partner-Attribution-Id
        $this->setPartnerAttributionId(PartnerAttributionId::PAYPAL_CLASSIC); //Default
    }

    public function configure(array $settings)
    {
        $this->shopId = $settings['shopId'];
        $environment = $settings['sandbox'];
        $environment === true ? $this->baseUrl = BaseURL::SANDBOX : $this->baseUrl = BaseURL::LIVE;

        //Create authentication
        $credentials = new OAuthCredentials();
        $credentials->setRestId($settings['clientId']);
        $credentials->setRestSecret($settings['clientSecret']);
        $this->createAuthentication($credentials);
    }

    /**
     * Sends a request and returns the response.
     * The type can be obtained from RequestType.php
     *
     * @param string     $type
     * @param string     $resourceUri
     * @param array|null $data
     * @param bool       $jsonPayload
     *
     * @throws RequestException
     *
     * @return array
     */
    public function sendRequest($type, $resourceUri, $data = [], $jsonPayload = true)
    {
        if (!$this->getHeader('Authorization')) {
            $environment = (bool) $this->settingsService->get('sandbox');
            $environment === true ? $this->baseUrl = BaseURL::SANDBOX : $this->baseUrl = BaseURL::LIVE;

            //Create authentication
            $credentials = new OAuthCredentials();
            $credentials->setRestId($this->settingsService->get('client_id'));
            $credentials->setRestSecret($this->settingsService->get('client_secret'));
            $this->createAuthentication($credentials);
        }

        $resourceUri = $this->baseUrl . $resourceUri;

        if ($jsonPayload) {
            $data = \json_encode($data);
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

        $this->logger->notify('Received data from ' . $resourceUri, ['payload' => $response]);

        return \json_decode($response, true);
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
     * Creates the authentication header for the PayPal API.
     * If there is no cached token yet, it will be generated on the fly.
     *
     * @throws RequestException
     */
    private function createAuthentication(OAuthCredentials $credentials)
    {
        try {
            $token = $this->tokenService->getToken($this, $credentials, $this->shopId);
            $this->setHeader('Authorization', $token->getTokenType() . ' ' . $token->getAccessToken());
        } catch (RequestException $requestException) {
            $this->logger->error('Could not create authentication - request exception', [
                'payload' => $requestException->getBody(),
                'message' => $requestException->getMessage(),
            ]);

            throw $requestException;
        } catch (\Exception $e) {
            $this->logger->error('Could not create authentication - unknown exception', [
                'message' => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString(),
            ]);
        }
    }
}
