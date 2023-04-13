<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;
use Shopware_Components_Translation as TranslationWriter;
use SwagPaymentPayPalUnified\Setup\TranslationUpdater;

class UpdateTo604
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TranslationWriter
     */
    private $translationWriter;

    public function __construct(Connection $connection, TranslationWriter $translationWriter)
    {
        $this->connection = $connection;
        $this->translationWriter = $translationWriter;
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->updateGeneralSettingsButtonStyleSize();
        $this->updateExpressSettingsButtonStyleSize();
        $this->updatePayLaterTranslations();
    }

    /**
     * @return void
     */
    private function updatePayLaterTranslations()
    {
        $translationUpdater = new TranslationUpdater($this->connection, $this->translationWriter);
        $translationUpdater->updateTranslationForAllShops();
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
