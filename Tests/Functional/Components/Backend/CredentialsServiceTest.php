<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Backend;

use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Backend\CredentialsService;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\CredentialsResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Services\TokenService;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class CredentialsServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use ContainerTrait;

    /**
     * @dataProvider updateCredentialsTestDataProvider
     *
     * @param bool $sandbox
     *
     * @return void
     */
    public function testUpdateCredentials($sandbox)
    {
        $shopId = 12;

        $credentials = [
            'client_id' => 'this-is-the-client-id-for-unit-testing',
            'client_secret' => 'this-is-the-client-secret-for-unit-testing',
            'payer_id' => 'this-is-the-payer-id-for-unit-testing',
        ];

        $this->createCredentialsService($shopId)->updateCredentials($credentials, $shopId, $sandbox);

        $result = $this->getContainer()->get('paypal_unified.settings_service')->getSettings($shopId);

        $this->deleteGeneralSettingFromDatabase($shopId);

        static::assertInstanceOf(General::class, $result);

        static::assertSame($credentials['client_id'], $sandbox ? $result->getSandboxClientId() : $result->getClientId());
        static::assertSame($credentials['client_secret'], $sandbox ? $result->getSandboxClientSecret() : $result->getClientSecret());
        static::assertSame($credentials['payer_id'], $sandbox ? $result->getSandboxPaypalPayerId() : $result->getPaypalPayerId());
    }

    /**
     * @return Generator<array<int,bool>>
     */
    public function updateCredentialsTestDataProvider()
    {
        yield 'Update credentials with sandbox true' => [
            true,
        ];

        yield 'Update credentials with sandbox false' => [
            false,
        ];
    }

    /**
     * @param int $shopId
     *
     * @return CredentialsService
     */
    private function createCredentialsService($shopId)
    {
        $credentialsResourceMock = $this->createMock(CredentialsResource::class);

        $settingsServiceMock = $this->createMock(SettingsServiceInterface::class);
        $settingsServiceMock->method('getSettings')->willReturn($this->createGeneralSettings($shopId));

        $loggerServiceMock = $this->createMock(LoggerServiceInterface::class);

        $clientServiceMock = $this->createMock(ClientService::class);

        $tokenServiceMock = $this->createMock(TokenService::class);

        $entityManager = $this->getContainer()->get('models');

        return new CredentialsService(
            $credentialsResourceMock,
            $settingsServiceMock,
            $entityManager,
            $loggerServiceMock,
            $clientServiceMock,
            $tokenServiceMock
        );
    }

    /**
     * @param int $shopId
     *
     * @return General
     */
    private function createGeneralSettings($shopId)
    {
        $generalSettings = new General();
        $generalSettings->setShopId((string) $shopId);
        $generalSettings->setActive(true);
        $generalSettings->setShowSidebarLogo(false);
        $generalSettings->setDisplayErrors(false);
        $generalSettings->setUseSmartPaymentButtons(false);
        $generalSettings->setSubmitCart(false);

        return $generalSettings;
    }

    /**
     * @param int $shopId
     *
     * @return void
     */
    private function deleteGeneralSettingFromDatabase($shopId)
    {
        $sql = 'DELETE FROM swag_payment_paypal_unified_settings_general where shop_id = ?';

        $this->getContainer()->get('db')->executeQuery($sql, [$shopId]);
    }
}
