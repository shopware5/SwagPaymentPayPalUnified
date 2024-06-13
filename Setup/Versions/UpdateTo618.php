<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;
use SwagPaymentPayPalUnified\Setup\InstanceIdService;

class UpdateTo618
{
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->createInstanceTable();

        (new InstanceIdService($this->connection))->getInstanceId();
    }

    /**
     * @return void
     */
    private function createInstanceTable()
    {
        $this->connection->executeQuery(
            '
            CREATE TABLE IF NOT EXISTS `swag_payment_paypal_unified_instance`
            (
                `instance_id` VARCHAR(255) NOT NULL
            ) ENGINE = InnoDB
                DEFAULT CHARSET = utf8
                COLLATE = utf8_unicode_ci;'
        );
    }
}
