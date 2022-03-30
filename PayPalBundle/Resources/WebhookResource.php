<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Resources;

use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\RequestUri;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;

class WebhookResource
{
    /**
     * @var ClientService
     */
    private $client;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function __construct(ClientService $client, LoggerServiceInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getList()
    {
        $this->logger->debug(sprintf('%s GET LIST', __METHOD__));

        return $this->client->sendRequest(RequestType::GET, RequestUri::WEBHOOK_RESOURCE);
    }

    /**
     * @param string $url
     *
     * @return array
     */
    public function create($url, array $events)
    {
        $this->logger->debug(sprintf('%s CREATE WITH URL: %s', __METHOD__, $url));

        $data = [
            'url' => $url,
            'event_types' => [],
        ];

        foreach ($events as $event) {
            $data['event_types'][] = [
                'name' => $event,
            ];
        }

        return $this->client->sendRequest(RequestType::POST, RequestUri::WEBHOOK_RESOURCE, $data);
    }
}
