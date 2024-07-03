<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;
use Exception;
use SwagPaymentPayPalUnified\Setup\InstanceIdService;

class UpdateTo618
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
        $this->deactivateGiropayPayment();
        $this->createInstanceTable();

        try {
            (new InstanceIdService($this->connection))->getInstanceId();
        } catch (Exception $e) {
            // no need to handle this exception
        }
    }

    /**
     * @return void
     */
    private function deactivateGiropayPayment()
    {
        $this->connection->executeQuery(
            'UPDATE s_core_paymentmeans SET active = 0 WHERE name = "SwagPaymentPayPalUnifiedGiropay"'
        );
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
                `instance_id` VARCHAR(36) NOT NULL
            ) ENGINE = InnoDB
                DEFAULT CHARSET = utf8
                COLLATE = utf8_unicode_ci;'
        );
    }
}
