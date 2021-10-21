<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Resources;

use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\RequestUri;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\OAuthCredentials;

class TokenResource
{
    /**
     * @var ClientService
     */
    private $client;

    public function __construct(ClientService $client)
    {
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function get(OAuthCredentials $credentials)
    {
        $data = [
            'grant_type' => 'client_credentials',
        ];

        //Set the header temporarily for this request
        $this->client->setHeader('Authorization', $credentials->toString());

        return $this->client->sendRequest(RequestType::POST, RequestUri::TOKEN_RESOURCE, $data, false);
    }
}
