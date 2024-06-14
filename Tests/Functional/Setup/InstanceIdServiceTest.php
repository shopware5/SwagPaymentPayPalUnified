<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Setup\InstanceIdService;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;

class InstanceIdServiceTest extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testGetInstanceId()
    {
        $connection = $this->getContainer()->get('dbal_connection');
        $instanceIdService = new InstanceIdService($connection);

        $instanceId = $instanceIdService->getInstanceId();
        static::assertSame(36, \strlen($instanceId));
        static::assertNotEmpty($instanceId);

        $connection->executeQuery('DELETE FROM swag_payment_paypal_unified_instance WHERE true');

        $instanceId = $instanceIdService->getInstanceId();
        static::assertSame(36, \strlen($instanceId));
        static::assertNotEmpty($instanceId);
    }
}
