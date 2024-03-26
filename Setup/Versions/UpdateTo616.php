<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;

class UpdateTo616
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
        $this->createRefundInfoTable();
    }

    /**
     * @return void
     */
    private function createRefundInfoTable()
    {
        $this->connection->executeQuery(
            '
            CREATE TABLE IF NOT EXISTS `swag_payment_paypal_unified_order_refund_info`
            (
                `id`              INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `paypal_order_id` VARCHAR(255) NOT NULL,
                `order_amount`    VARCHAR(255) NOT NULL,
                `currency`        VARCHAR(10) NOT NULL,
                `created_at`      DATETIME NOT NULL
            ) ENGINE = InnoDB
              DEFAULT CHARSET = utf8
              COLLATE = utf8_unicode_ci;'
        );
    }
}
