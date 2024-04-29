<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;

class UpdateTo617
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->createOrderTurnoverTable();
    }

    /**
     * @return void
     */
    private function createOrderTurnoverTable()
    {
        $this->connection->executeQuery(
            '
            CREATE TABLE IF NOT EXISTS `swag_payment_paypal_unified_transaction_report`
            (
                `id`       INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `order_id` INT(11) NOT NULL
            ) ENGINE = InnoDB
                DEFAULT CHARSET = utf8
                COLLATE = utf8_unicode_ci;'
        );
    }
}
