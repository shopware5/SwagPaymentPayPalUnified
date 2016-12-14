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

use SwagPaymentPayPalUnified\SDK\Resources\WebhookResource;
use SwagPaymentPayPalUnified\SDK\Structs\Webhook;

class WebhookGuardService
{
    /** @var ClientService $client */
    private $client;

    /** @var string[] $remoteWebhookIds */
    private $remoteWebhookIds;

    /**
     * @param ClientService $client
     */
    public function __construct(ClientService $client)
    {
        $this->client = $client;

        $this->remoteWebhookIds = $this->getWebhookList();
    }

    /**
     * @param Webhook $webhookHeader
     * @return bool
     */
    public function isValid(Webhook $webhookHeader)
    {
        if (count($this->remoteWebhookIds) === 0) {
            $this->remoteWebhookIds = $this->getWebhookList();
        }

        if ($webhookHeader->getId() === null) {
            return false;
        }

        return $this->remoteWebhookIds[$webhookHeader->getId()] !== null;
    }

    /**
     * @return string[]
     */
    private function getWebhookList()
    {
        $webhookResource = new WebhookResource($this->client);

        $response = $webhookResource->getList();
        $remoteWebhookIds = [];

        foreach ($response['webhooks'] as $webhook) {
            $remoteWebhookIds[$webhook['id']] = $webhook['url'];
        }

        return $remoteWebhookIds;
    }
}
