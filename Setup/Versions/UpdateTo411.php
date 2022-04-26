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

class UpdateTo411
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
        $this->addOrderStatusOnFailedPaymentColumn();
        $this->addPaymentStatusOnFailedPaymentColumn();
    }

    /**
     * @return void
     */
    private function addOrderStatusOnFailedPaymentColumn()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'order_status_on_failed_payment')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                ADD `order_status_on_failed_payment` INT(11) default -1;'
            );
        }
    }

    /**
     * @return void
     */
    private function addPaymentStatusOnFailedPaymentColumn()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'payment_status_on_failed_payment')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                ADD `payment_status_on_failed_payment` INT(11) default 35;'
            );
        }
    }
}
