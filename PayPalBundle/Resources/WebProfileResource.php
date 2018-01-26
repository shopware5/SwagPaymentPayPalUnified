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
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile;

class WebProfileResource
{
    /**
     * @var ClientService
     */
    private $client;

    /**
     * @param ClientService $client
     */
    public function __construct(ClientService $client)
    {
        $this->client = $client;
    }

    /**
     * @param WebProfile $profile
     *
     * @return WebProfile
     */
    public function create(WebProfile $profile)
    {
        $payload = $profile->toArray();
        $data = $this->client->sendRequest(RequestType::POST, RequestUri::PROFILE_RESOURCE, $payload);

        return WebProfile::fromArray($data);
    }

    /**
     * @return array
     */
    public function getList()
    {
        return $this->client->sendRequest(RequestType::GET, RequestUri::PROFILE_RESOURCE . '/');
    }

    /**
     * @param string     $remoteProfileId
     * @param WebProfile $profile
     */
    public function update($remoteProfileId, WebProfile $profile)
    {
        $payload = $profile->toArray();
        $this->client->sendRequest(RequestType::PUT, RequestUri::PROFILE_RESOURCE . '/' . $remoteProfileId, $payload);
    }
}
