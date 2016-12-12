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

namespace SwagPaymentPayPalUnified\SDK\Resources;

use SwagPaymentPayPalUnified\SDK\RequestType;
use SwagPaymentPayPalUnified\SDK\Services\ClientService;
use SwagPaymentPayPalUnified\SDK\Structs\WebProfile;

class WebProfileResource
{
    const PROFILE_RESOURCE = 'payment-experience/web-profiles';

    /** @var ClientService $client */
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
     * @return WebProfile
     */
    public function create(WebProfile $profile)
    {
        $payload = $profile->toArray();
        $data = $this->client->sendRequest(RequestType::POST, self::PROFILE_RESOURCE, $payload);

        return WebProfile::fromArray($data);
    }

    /**
     * @param string $id
     * @return WebProfile
     */
    public function get($id)
    {
        $data = $this->client->sendRequest(RequestType::GET, self::PROFILE_RESOURCE . '/' . $id);
        return WebProfile::fromArray($data);
    }

    /**
     * @return WebProfile[]
     */
    public function getList()
    {
        $data = $this->client->sendRequest(RequestType::GET, self::PROFILE_RESOURCE . '/');

        $result = [];
        foreach ($data as $profile) {
            $result[] = WebProfile::fromArray($profile);
        }

        return $result;
    }

    /**
     * @param string $remoteProfileId
     * @param WebProfile $profile
     */
    public function update($remoteProfileId, WebProfile $profile)
    {
        $payload = $profile->toArray();
        $this->client->sendRequest(RequestType::PUT, self::PROFILE_RESOURCE . '/'. $remoteProfileId, $payload);
    }
}
