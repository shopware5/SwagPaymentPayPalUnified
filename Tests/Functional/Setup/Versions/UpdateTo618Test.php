<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
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
        $firstResult = $this->getInstanceId($connection);
        static::assertNotEmpty($firstResult);
        $updater->update();

        static::assertTrue($this->checkTableExists($connection, 'swag_payment_paypal_unified_instance'));

        $secondResult = $this->getInstanceId($connection);
        static::assertNotEmpty($secondResult);

        static::assertSame($firstResult, $secondResult);
    }

    /**
     * @return string
     */
    private function getInstanceId(Connection $connection)
    {
        return $connection->createQueryBuilder()
            ->select('instance_id')
            ->from('swag_payment_paypal_unified_instance')
            ->execute()
            ->fetchColumn();
    }
}
