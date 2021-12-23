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
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\CredentialsResource;
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

    public function __construct(
        CredentialsResource $credentialsResource,
        SettingsServiceInterface $settingsService,
        EntityManagerInterface $entityManager
    ) {
        $this->credentialsResource = $credentialsResource;
        $this->settingsService = $settingsService;
        $this->entityManager = $entityManager;
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
        $settings = $this->settingsService->getSettings($shopId, SettingsTable::GENERAL);

        if (!$settings instanceof General) {
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
    }
}
