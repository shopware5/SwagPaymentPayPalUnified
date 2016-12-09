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

namespace SwagPaymentPayPalUnified\Tests\Functional\SDK\Services;

use SwagPaymentPayPalUnified\SDK\Services\TokenService;
use SwagPaymentPayPalUnified\SDK\Structs\Token;

class TokenServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_is_service_available()
    {
        $service = Shopware()->Container()->get('paypal_unified.token_service');

        $this->assertNotNull($service);
    }

    public function test_set_and_get_token()
    {
        $testToken = new Token();
        $testToken->setExpireDateTime(new \DateTime());
        $testToken->setTokenType('testType');
        $testToken->setScope('testScope');
        $testToken->setAccessToken('testAccessToken');
        $testToken->setExpiresIn(50);

        /** @var TokenService $service */
        $service = Shopware()->Container()->get('paypal_unified.token_service');
        $service->setToken($testToken);

        $testToken = $service->getCachedToken();

        //If no token was cached, it would return "false", not null.
        $this->assertNotFalse($testToken);
        $this->assertEquals('testType', $testToken->getTokenType());
        $this->assertEquals('testAccessToken', $testToken->getAccessToken());
    }

    public function test_remove_token()
    {
        $testToken = new Token();
        $testToken->setExpireDateTime(new \DateTime());
        $testToken->setTokenType('testType');
        $testToken->setScope('testScope');
        $testToken->setAccessToken('testAccessToken');
        $testToken->setExpiresIn(50);

        /** @var TokenService $service */
        $service = Shopware()->Container()->get('paypal_unified.token_service');
        $service->setToken($testToken);

        $service->removeToken();

        $testToken = $service->getCachedToken();

        $this->assertFalse($testToken);
    }

    public function test_validate_token_is_valid()
    {
        $testToken = new Token();
        $expireDate = new \DateTime();
        $expireDate->add(new \DateInterval('PT6H')); //+6h

        $testToken->setExpireDateTime($expireDate);
        $testToken->setTokenType('testType');
        $testToken->setScope('testScope');
        $testToken->setAccessToken('testAccessToken');
        $testToken->setExpiresIn(0); //>1hour

        /** @var TokenService $service */
        $service = Shopware()->Container()->get('paypal_unified.token_service');
        $service->setToken($testToken);

        $this->assertTrue($service->isValid($testToken));
    }

    public function test_validate_token_is_not_valid()
    {
        $testToken = new Token();
        $testToken->setExpireDateTime(new \DateTime());
        $testToken->setTokenType('testType');
        $testToken->setScope('testScope');
        $testToken->setAccessToken('testAccessToken');
        $testToken->setExpiresIn(500);

        /** @var TokenService $service */
        $service = Shopware()->Container()->get('paypal_unified.token_service');

        $this->assertFalse($service->isValid($testToken));
    }
}
