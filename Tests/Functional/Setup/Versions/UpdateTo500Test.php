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
use SwagPaymentPayPalUnified\Setup\ColumnService;
use SwagPaymentPayPalUnified\Setup\Versions\UpdateTo500;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseHelperTrait;

class UpdateTo500Test extends TestCase
{
    use ContainerTrait;
    use DatabaseHelperTrait;

    /**
     * @return void
     */
    public function testUpdate()
    {
        $connection = $this->getContainer()->get('dbal_connection');

        $connection->exec('ALTER TABLE `swag_payment_paypal_unified_settings_general` ADD COLUMN `send_order_number` TINYINT(1) NOT NULL DEFAULT 0;');
        $connection->exec('ALTER TABLE `swag_payment_paypal_unified_settings_general` ADD COLUMN `payment_status_on_failed_payment` INT(11) DEFAULT 35;');
        $connection->exec('ALTER TABLE `swag_payment_paypal_unified_settings_general` ADD COLUMN `order_status_on_failed_payment` INT(11)  DEFAULT -1;');
        $connection->exec('DROP TABLE IF EXISTS `swag_payment_paypal_unified_order_number_pool`');

        // Makes sure the tables has the state before the update
        static::assertTrue($this->checkIfColumnExist($connection, 'swag_payment_paypal_unified_settings_general', 'send_order_number'));
        static::assertTrue($this->checkIfColumnExist($connection, 'swag_payment_paypal_unified_settings_general', 'payment_status_on_failed_payment'));
        static::assertTrue($this->checkIfColumnExist($connection, 'swag_payment_paypal_unified_settings_general', 'order_status_on_failed_payment'));
        static::assertFalse($this->checkTableExists($connection, 'swag_payment_paypal_unified_order_number_pool'));

        // Executes the update twice
        $this->getUpdater($connection)->update();
        $this->getUpdater($connection)->update();

        static::assertFalse($this->checkIfColumnExist($connection, 'swag_payment_paypal_unified_settings_general', 'send_order_number'));
        static::assertFalse($this->checkIfColumnExist($connection, 'swag_payment_paypal_unified_settings_general', 'payment_status_on_failed_payment'));
        static::assertFalse($this->checkIfColumnExist($connection, 'swag_payment_paypal_unified_settings_general', 'order_status_on_failed_payment'));
        static::assertTrue($this->checkTableExists($connection, 'swag_payment_paypal_unified_order_number_pool'));
    }

    /**
     * @return UpdateTo500
     */
    private function getUpdater(Connection $connection)
    {
        return new UpdateTo500($connection, new ColumnService($connection));
    }
}
