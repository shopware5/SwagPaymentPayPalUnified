<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Resources;

use Shopware\Components\HttpClient\HttpClientInterface;
use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\PayPalBundle\BaseURL;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\RequestUri;

class CredentialsResource
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function __construct(HttpClientInterface $client, LoggerServiceInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @param string $authCode
     * @param string $sharedId
     * @param string $nonce
     * @param bool   $sandbox
     *
     * @throws RequestException
     *
     * @return string
     */
    public function getAccessToken($authCode, $sharedId, $nonce, $sandbox)
    {
        $this->logger->debug(
            sprintf(
                '%s AUTHCODE: %s, SHARED ID: %s, NONCE: %s, SANDBOX: %s',
                __METHOD__,
                $authCode,
                $sharedId,
                $nonce,
                $sandbox ? 'TRUE' : 'FALSE'
            )
        );

        $data = [
            'grant_type' => 'authorization_code',
            'code' => $authCode,
            'code_verifier' => $nonce,
        ];

        $response = $this->client->post(
            sprintf('%s%s', $sandbox ? BaseURL::SANDBOX : BaseURL::LIVE, RequestUri::TOKEN_RESOURCE),
            [
                'Authorization' => sprintf('Basic %s', base64_encode(sprintf('%s:', $sharedId))),
            ],
            $data
        );

        return json_decode($response->getBody(), true)['access_token'];
    }

    /**
     * @param string $accessToken
     * @param string $partnerId
     * @param bool   $sandbox
     *
     * @throws RequestException
     *
     * @return array{client_id: string, client_secret: string, payer_id: string}
     */
    public function getCredentials($accessToken, $partnerId, $sandbox)
    {
        $this->logger->debug(
            sprintf(
                '%s ACCESS TOKEN: %s, PARTNER ID: %s, SANDBOX: %s',
                __METHOD__,
                $accessToken,
                $partnerId,
                $sandbox ? 'TRUE' : 'FALSE'
            )
        );

        $response = $this->client->get(
            sprintf('%s%s', $sandbox ? BaseURL::SANDBOX : BaseURL::LIVE, sprintf(RequestUri::CREDENTIALS_RESOURCE, $partnerId)),
            [
                'Authorization' => sprintf('Bearer %s', $accessToken),
            ]
        );

        return json_decode($response->getBody(), true);
    }
}
