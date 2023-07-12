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

class UpdateTo610
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
        $this->addAdvancedCreditDebitCardSettingWithAuthenticationSystemOnly();
    }

    /**
     * @return void
     */
    private function addAdvancedCreditDebitCardSettingWithAuthenticationSystemOnly()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_advanced_credit_debit_card', 'block_cards_from_non_three_ds_countries')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_advanced_credit_debit_card`
                ADD `block_cards_from_non_three_ds_countries` TINYINT(1) NOT NULL default 0;'
            );
        }
    }
}
