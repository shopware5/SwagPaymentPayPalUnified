<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Backend;

use Doctrine\ORM\EntityManagerInterface;
use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\CredentialsResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use UnexpectedValueException;

class CredentialsService
{
    /**
     * @var CredentialsResource
     */
    private $credentialsResource;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var ClientService
     */
    private $clientService;

    public function __construct(
        CredentialsResource $credentialsResource,
        SettingsServiceInterface $settingsService,
        EntityManagerInterface $entityManager,
        LoggerServiceInterface $logger,
        ClientService $clientService
    ) {
        $this->credentialsResource = $credentialsResource;
        $this->settingsService = $settingsService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->clientService = $clientService;
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

        return $this->credentialsResource->getAccessToken($authCode, $sharedId, $nonce, $sandbox);
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
        $this->logger->debug(
            sprintf(
                '%s ACCESS TOKEN: %s, PARTNER ID: %s, SANDBOX: %s',
                __METHOD__,
                $accessToken,
                $partnerId,
                $sandbox ? 'TRUE' : 'FALSE'
            )
        );

        return $this->credentialsResource->getCredentials($accessToken, $partnerId, $sandbox);
    }

    /**
     * @param array{client_id: string, client_secret: string} $credentials
     * @param int                                             $shopId
     * @param bool                                            $sandbox
     *
     * @throws UnexpectedValueException
     *
     * @return void
     */
    public function updateCredentials($credentials, $shopId, $sandbox)
    {
        $this->logger->debug(
            sprintf(
                '%s SHOP ID: %s, SANDBOX: %s',
                __METHOD__,
                $shopId,
                $sandbox ? 'TRUE' : 'FALSE'
            ),
            $credentials
        );

        $settings = $this->settingsService->getSettings($shopId);

        if (!$settings instanceof General) {
            $this->logger->debug(sprintf('%s SETTINGS NOT FOUND', __METHOD__));
            throw new UnexpectedValueException(sprintf('Expected instance of %s, got %s.', General::class, $settings === null ? 'null' : \get_class($settings)));
        }

        $settings->setSandbox($sandbox);

        if ($sandbox) {
            $settings->setSandboxClientId($credentials['client_id']);
            $settings->setSandboxClientSecret($credentials['client_secret']);
        } else {
            $settings->setClientId($credentials['client_id']);
            $settings->setClientSecret($credentials['client_secret']);
        }

        $this->entityManager->persist($settings);
        $this->entityManager->flush();

        $this->clientService->configure([
            'sandbox' => $settings->getSandbox(),
            'shopId' => $shopId,
            'clientId' => $settings->getClientId(),
            'clientSecret' => $settings->getClientSecret(),
            'sandboxClientId' => $settings->getSandboxClientId(),
            'sandboxClientSecret' => $settings->getSandboxClientSecret(),
        ]);

        $this->logger->debug(sprintf('%s SUCCESSFUL', __METHOD__));
    }
}
