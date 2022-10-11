<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;
use SwagPaymentPayPalUnified\Setup\ColumnService;

class UpdateTo500
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ColumnService
     */
    private $columnService;

    public function __construct(Connection $connection, ColumnService $columnService)
    {
        $this->connection = $connection;
        $this->columnService = $columnService;
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->createOrderNumberPoolTable();
        $this->removeSendOrderNumberColumn();
        $this->removePaymentStatusOnFailedPaymentColumn();
        $this->removeOrderStatusOnFailedPaymentColumn();
    }

    /**
     * @return void
     */
    private function removeSendOrderNumberColumn()
    {
        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'send_order_number')) {
            $sql = '
                ALTER TABLE `swag_payment_paypal_unified_settings_general`
                DROP COLUMN `send_order_number`;
            ';

            $this->connection->executeQuery($sql);
        }
    }

    /**
     * @return void
     */
    private function removePaymentStatusOnFailedPaymentColumn()
    {
        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'payment_status_on_failed_payment')) {
            $sql = '
                ALTER TABLE `swag_payment_paypal_unified_settings_general`
                DROP COLUMN `payment_status_on_failed_payment`;
            ';

            $this->connection->executeQuery($sql);
        }
    }

    /**
     * @return void
     */
    private function removeOrderStatusOnFailedPaymentColumn()
    {
        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'order_status_on_failed_payment')) {
            $sql = '
                ALTER TABLE `swag_payment_paypal_unified_settings_general`
                DROP COLUMN `order_status_on_failed_payment`;
            ';

            $this->connection->executeQuery($sql);
        }
    }

    /**
     * @return void
     */
    private function createOrderNumberPoolTable()
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `swag_payment_paypal_unified_order_number_pool`
            (
                `id`           INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `order_number` VARCHAR(255) NOT NULL
            ) ENGINE = InnoDB
              DEFAULT CHARSET = utf8
              COLLATE = utf8_unicode_ci;
        ';

        $this->connection->executeQuery($sql);
    }
}
