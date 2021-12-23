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
use SwagPaymentPayPalUnified\PayPalBundle\RequestUri;

class CredentialsResource
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
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
     * @return array{client_id: string, client_secret: string}
     */
    public function getCredentials($accessToken, $partnerId, $sandbox)
    {
        $response = $this->client->get(
            sprintf('%s%s', $sandbox ? BaseURL::SANDBOX : BaseURL::LIVE, sprintf(RequestUri::CREDENTIALS_RESOURCE, $partnerId)),
            [
                'Authorization' => sprintf('Bearer %s', $accessToken),
            ]
        );

        return json_decode($response->getBody(), true);
    }
}
