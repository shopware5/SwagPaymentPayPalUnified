<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class InstallerTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function testOrderAttributeAvailable()
    {
        $query = "SELECT 1
                    FROM information_schema.COLUMNS
                    WHERE TABLE_NAME = 's_order_attributes'
                    AND COLUMN_NAME = 'swag_paypal_unified_payment_type'";

        $connection = Shopware()->Container()->get('dbal_connection');
        $columnAvailable = (bool) $connection->executeQuery($query)->fetch(\PDO::FETCH_COLUMN);

        static::assertTrue($columnAvailable);
    }

    public function testInstructionsTableExists()
    {
        $query = "SHOW TABLES LIKE 'swag_payment_paypal_unified_payment_instruction'";

        $result = Shopware()->Db()->fetchAll($query);
        static::assertTrue(\is_array($result));
        static::assertCount(1, $result);
    }

    public function testDocumentFooterTemplateExists()
    {
        $query = "SELECT id FROM s_core_documents_box WHERE `name` = 'PayPal_Unified_Instructions_Footer'";

        $result = Shopware()->Db()->fetchRow($query);
        static::assertTrue(\is_array($result));
        static::assertCount(1, $result);
    }

    public function testDocumentContentTemplateExists()
    {
        $query = "SELECT id FROM s_core_documents_box WHERE `name` = 'PayPal_Unified_Instructions_Content'";

        $result = Shopware()->Db()->fetchRow($query);
        static::assertTrue(\is_array($result));
        static::assertCount(1, $result);
    }

    public function testSettingsTablesExists()
    {
        $query = "SHOW TABLES LIKE 'swag_payment_paypal_unified_settings_express';";
        $result = Shopware()->Db()->fetchAll($query);
        static::assertTrue(\is_array($result));
        static::assertCount(1, $result);

        $query = "SHOW TABLES LIKE 'swag_payment_paypal_unified_settings_installments';";
        $result = Shopware()->Db()->fetchAll($query);
        static::assertTrue(\is_array($result));
        static::assertCount(1, $result);

        $query = "SHOW TABLES LIKE 'swag_payment_paypal_unified_settings_plus';";
        $result = Shopware()->Db()->fetchAll($query);
        static::assertTrue(\is_array($result));
        static::assertCount(1, $result);

        $query = "SHOW TABLES LIKE 'swag_payment_paypal_unified_settings_general';";
        $result = Shopware()->Db()->fetchAll($query);
        static::assertTrue(\is_array($result));
        static::assertCount(1, $result);
    }
}
