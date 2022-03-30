<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Backend;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Backend\CredentialsService;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\CredentialsResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;

class CredentialsServiceTest extends TestCase
{
    const SHOP_ID = 458787540;

    const ACCESS_TOKEN = '1fedc337-b017-4cc9-9996-bc7606cf80e2';
    const ACCESS_TOKEN_RETRIEVAL_EXCEPTION = 'ce75c680-6acc-4551-aefe-84f035296cf9';

    const CREDENTIALS = [
        'client_id' => '392120f1-4022-46bd-a4a0-61df3d02c415',
        'client_secret' => '7c93ff81-ab7a-4b0e-97c9-be62ff5b8ea6',
    ];
    const CREDENTIALS_RETRIEVAL_EXCEPTION = '00ae310f-8729-46f8-a7fe-fc2ddb7dc247';

    const UNEXPECTED_VALUE_EXCEPTION_MESSAGE = '/Expected instance of (.*), got (.*)\./';

    /**
     * @var MockObject|CredentialsResource
     */
    private $credentialsResource;

    /**
     * @var MockObject|SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var MockObject|EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MockObject|General
     */
    private $generalSettings;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @before
     */
    public function init()
    {
        $this->credentialsResource = static::createMock(CredentialsResource::class);
        $this->settingsService = static::createMock(SettingsServiceInterface::class);
        $this->entityManager = static::createMock(EntityManagerInterface::class);
        $this->logger = static::createMock(LoggerServiceInterface::class);
        $this->clientService = static::createMock(ClientService::class);

        $this->generalSettings = static::createMock(General::class);
    }

    public function testGetAccessTokenReturnsTokenOnSuccess()
    {
        $this->givenTheresAnAccessToken();

        static::assertSame(
            self::ACCESS_TOKEN,
            $this->getCredentialsService()->getAccessToken(
                '08c0dc0f-661e-41c9-9f76-1ced744d0c66',
                '3a3af1cb-be28-421e-b3a2-8651c04c3562',
                '899759b7-330c-409b-9959-5491b89e4bd7',
                true
            )
        );
    }

    public function testGetAccessTokenBubblesExceptions()
    {
        $this->givenTheresAnErrorWhenRetrievingAnAccessToken();

        static::expectException(\Exception::class);
        static::expectExceptionMessage(self::ACCESS_TOKEN_RETRIEVAL_EXCEPTION);

        $this->getCredentialsService()->getAccessToken(
            '1f21d971-1049-476d-994b-da613eb561c7',
            '0319a288-8afe-4b80-9553-664887dfb987',
            '8c7f994b-b7ac-44e5-a337-14bbaa04b413',
            true
        );
    }

    public function testGetCredentialsReturnsTokenOnSuccess()
    {
        $this->givenThereAreCredentials();

        static::assertSame(
            self::CREDENTIALS,
            $this->getCredentialsService()->getCredentials(
                'f4d0605b-2bb1-4256-8064-3009716ab3ab',
                'ec3737ff-b80c-4ef9-ad11-a3d8a89dfbe3',
                'd9804e77-ac3f-4809-a380-eff99f68748f'
            )
        );
    }

    public function testGetCredentialsBubblesExceptions()
    {
        $this->givenTheresAnErrorWhenRetrievingCredentials();

        static::expectException(\Exception::class);
        static::expectExceptionMessage(self::CREDENTIALS_RETRIEVAL_EXCEPTION);

        $this->getCredentialsService()->getCredentials(
            '13c1b5a0-6373-4a64-bdaa-f04105a50f3f',
            '0189ae21-4a2e-4080-bced-cce117f79b92',
            '45d8787f-ebb5-4a07-8b49-4ab8605d8999'
        );
    }

    /**
     * @param bool $sandbox
     *
     * @dataProvider sandboxDataProvider
     */
    public function testUpdateCredentialsThrowsExceptionWhenSettingsAreNotPresent($sandbox)
    {
        static::expectException(\UnexpectedValueException::class);

        if (\is_callable([TestCase::class, 'expectExceptionMessageMatches'])) {
            static::expectExceptionMessageMatches(self::UNEXPECTED_VALUE_EXCEPTION_MESSAGE);
        }

        $this->getCredentialsService()->updateCredentials([], self::SHOP_ID, $sandbox);
    }

    /**
     * @param bool $sandbox
     *
     * @dataProvider sandboxDataProvider
     */
    public function testUpdateCredentialsTakesSandboxSettingIntoAccount($sandbox)
    {
        $this->givenThereAreGeneralSettings();

        $this->expectTheSandboxParamToBeSetTo($sandbox);

        if ($sandbox) {
            $this->expectTheSandboxSettersToBeUsed();
        } else {
            $this->expectTheLiveSettersToBeUsed();
        }

        $this->getCredentialsService()->updateCredentials([], self::SHOP_ID, $sandbox);
    }

    public function sandboxDataProvider()
    {
        return [
            'Sandbox active' => [
                true,
            ],
            'Sandbox inactive' => [
                false,
            ],
        ];
    }

    protected function getCredentialsService(
        CredentialsResource $credentialsResource = null,
        SettingsServiceInterface $settingsService = null,
        EntityManagerInterface $entityManager = null,
        LoggerServiceInterface $loggerService = null,
        ClientService $clientService = null
    ) {
        return new CredentialsService(
            $credentialsResource ?: $this->credentialsResource,
            $settingsService ?: $this->settingsService,
            $entityManager ?: $this->entityManager,
            $loggerService ?: $this->logger,
            $clientService ?: $this->clientService
        );
    }

    private function givenTheresAnAccessToken()
    {
        $this->credentialsResource->method('getAccessToken')
            ->willReturn(self::ACCESS_TOKEN);
    }

    private function givenTheresAnErrorWhenRetrievingAnAccessToken()
    {
        $this->credentialsResource->method('getAccessToken')
            ->willThrowException(new \Exception(self::ACCESS_TOKEN_RETRIEVAL_EXCEPTION));
    }

    private function givenThereAreCredentials()
    {
        $this->credentialsResource->method('getCredentials')
            ->willReturn(self::CREDENTIALS);
    }

    private function givenTheresAnErrorWhenRetrievingCredentials()
    {
        $this->credentialsResource->method('getCredentials')
            ->willThrowException(new \Exception(self::CREDENTIALS_RETRIEVAL_EXCEPTION));
    }

    private function givenThereAreGeneralSettings()
    {
        $this->settingsService->method('getSettings')
            ->willReturnMap([
                [self::SHOP_ID, SettingsTable::GENERAL, $this->generalSettings],
            ]);
    }

    /**
     * @param bool $sandbox
     *
     * @return void
     */
    private function expectTheSandboxParamToBeSetTo($sandbox)
    {
        $this->generalSettings->expects(static::once())
            ->method('setSandbox')
            ->with($sandbox);
    }

    private function expectTheSandboxSettersToBeUsed()
    {
        $this->generalSettings->expects(static::once())
            ->method('setSandboxClientId');
    }

    private function expectTheLiveSettersToBeUsed()
    {
        $this->generalSettings->expects(static::once())
            ->method('setClientId');
    }
}
