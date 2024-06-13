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
use SwagPaymentPayPalUnified\Setup\Versions\UpdateTo618;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseHelperTrait;

class UpdateTo618Test extends TestCase
{
    use ContainerTrait;
    use DatabaseHelperTrait;

    /**
     * @return void
     */
    public function testUpdate()
    {
        $sql = 'DROP TABLE `swag_payment_paypal_unified_instance`';
        $connection = $this->getContainer()->get('dbal_connection');
        $connection->exec($sql);
        static::assertFalse($this->checkTableExists($connection, 'swag_payment_paypal_unified_instance'));

        $updater = new UpdateTo618($connection);
        $updater->update();
        $updater->update();

        static::assertTrue($this->checkTableExists($connection, 'swag_payment_paypal_unified_instance'));

        $result = $connection->createQueryBuilder()
            ->select('instance_id')
            ->from('swag_payment_paypal_unified_instance')
            ->execute()
            ->fetchColumn();

        static::assertNotEmpty($result);
    }
}
