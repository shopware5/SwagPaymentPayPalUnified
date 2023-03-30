<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;

class UpdateTo604
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
        $this->updateGeneralSettingsButtonStyleSize();
        $this->updateExpressSettingsButtonStyleSize();
    }

    /**
     * @return void
     */
    private function updateGeneralSettingsButtonStyleSize()
    {
        $sql = "UPDATE `swag_payment_paypal_unified_settings_general` SET `button_style_size` = 'responsive' WHERE true;";

        $this->connection->exec($sql);
    }

    /**
     * @return void
     */
    private function updateExpressSettingsButtonStyleSize()
    {
        $sql = "UPDATE `swag_payment_paypal_unified_settings_express` SET `button_style_size` = 'responsive' WHERE true;";

        $this->connection->exec($sql);
    }
}
