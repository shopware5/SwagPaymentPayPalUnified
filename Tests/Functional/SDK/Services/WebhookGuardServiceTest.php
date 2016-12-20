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

use SwagPaymentPayPalUnified\SDK\Services\ClientService;
use SwagPaymentPayPalUnified\SDK\Services\WebhookGuardService;
use SwagPaymentPayPalUnified\SDK\Structs\Webhook;

class WebhookGuardServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $expectedData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockedClient(array $expectedData = [])
    {
        $client = $this->createMock(ClientService::class);
        $client->method('sendRequest')->willReturn($expectedData);

        return $client;
    }

    public function test_can_be_created_and_initialized()
    {
        $sendRequestReturnData = [
            'webhooks' => [
                ['id' => 'YOU_SHOULD_PASS', 'url' => 'http://example.paypal.com/']
            ]

        ];

        $webhookGuard = new WebhookGuardService($this->getMockedClient($sendRequestReturnData));

        $this->assertEquals(WebhookGuardService::class, get_class($webhookGuard));
    }

    public function test_verify_should_be_invalid()
    {
        $sendRequestReturnData = [
            'webhooks' => [
                ['id' => 'YOU_SHOULD_PASS', 'url' => 'http://example.paypal.com/']
            ]
        ];

        $webhookGuard = new WebhookGuardService($this->getMockedClient($sendRequestReturnData));

        $hookThatShouldBeVerified = Webhook::fromArray([
            'id' => 'YOU_SHOULD_NOT_PASS'
        ]);


        $this->assertFalse($webhookGuard->isValid($hookThatShouldBeVerified));

        $hookThatShouldBeVerified = Webhook::fromArray([
            'id' => null
        ]);

        $this->assertFalse($webhookGuard->isValid($hookThatShouldBeVerified));
    }

    public function test_verify_should_be_valid()
    {
        $sendRequestReturnData = [
            'webhooks' => [
                ['id' => 'YOU_SHOULD_PASS', 'url' => 'http://example.paypal.com/']
            ]
        ];

        $webhookGuard = new WebhookGuardService($this->getMockedClient($sendRequestReturnData));

        $hookHeaderThatShouldBeVerified = Webhook::fromArray([
            'id' => 'YOU_SHOULD_PASS'
        ]);

        $this->assertTrue($webhookGuard->isValid($hookHeaderThatShouldBeVerified));
    }
}
