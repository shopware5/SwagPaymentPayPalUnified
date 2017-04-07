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

namespace SwagPaymentPayPalUnified\Tests\Functional\PayPalBundle\Services;

use Shopware\Components\HttpClient\GuzzleFactory;
use Shopware\Components\Logger;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Services\PartnerAttributionService;
use SwagPaymentPayPalUnified\PayPalBundle\Services\TokenService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Token;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class ClientServiceTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;

    public function test_partner_attribution_id_is_for_classic()
    {
        $clientService = $this->getClientService(false);

        $idToBeChecked = $clientService->getHeader('PayPal-Partner-Attribution-Id');

        $this->assertEquals(PartnerAttributionService::PARTNER_ID_PAYPAL_CLASSIC, $idToBeChecked);
    }

    public function test_partner_attribution_id_is_for_plus()
    {
        $clientService = $this->getClientService(true);

        $idToBeChecked = $clientService->getHeader('PayPal-Partner-Attribution-Id');

        $this->assertEquals(PartnerAttributionService::PARTNER_ID_PAYPAL_PLUS, $idToBeChecked);
    }

    private function getClientService($usePayPalPlus)
    {
        $this->createTestSettings($usePayPalPlus);

        /** @var SettingsService $config */
        $config = Shopware()->Container()->get('paypal_unified.settings_service');

        return new ClientService(
            $config,
            $this->getMockedTokenService(),
            new Logger('testLogger'),
            new GuzzleFactory(),
            Shopware()->Container()->get('paypal_unified.partner_attribution_service'),
            Shopware()->Container()->get('paypal_unified.dependency_provider')
        );
    }

    private function createTestSettings($usePayPalPlus)
    {
        $settingsParams = [
            ':shopId' => 1,
            ':active' => 1,
            ':plusActive' => $usePayPalPlus,
        ];

        $sql = 'INSERT INTO swag_payment_paypal_unified_settings
                (shop_id, active, plus_active)
                VALUES (:shopId, :active, :plusActive)';

        Shopware()->Db()->executeUpdate($sql, $settingsParams);
    }

    private function getMockedTokenService()
    {
        $tokenServiceMock = self::createMock(TokenService::class);
        $tokenServiceMock->method('getToken')->willReturn(new Token());

        return $tokenServiceMock;
    }
}
