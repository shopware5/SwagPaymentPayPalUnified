<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SwagPaymentPayPalUnified\Setup\Uninstaller;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;

class UninstallerTest extends TestCase
{
    use ContainerTrait;

    const TABLES = [
        'swag_payment_paypal_unified_settings_express',
        'swag_payment_paypal_unified_settings_general',
        'swag_payment_paypal_unified_settings_installments',
        'swag_payment_paypal_unified_settings_plus',
        'swag_payment_paypal_unified_settings_advanced_credit_debit_card',
        'swag_payment_paypal_unified_settings_pay_upon_invoice',
    ];

    /**
     * @return void
     */
    public function testRemoveSettingsTablesShouldRemoveAllTables()
    {
        $uninstaller = $this->createUninstaller();

        $reflectionMethod = (new ReflectionClass(Uninstaller::class))->getMethod('removeSettingsTables');
        $reflectionMethod->setAccessible(true);

        $reflectionMethod->invoke($uninstaller);

        $result = [];
        foreach (self::TABLES as $tableName) {
            $result[$tableName] = $this->checkTableExists($tableName);
        }

        $this->resetDatabase();

        static::assertCount(6, $result);
        foreach ($result as $value) {
            static::assertFalse($value);
        }
    }

    /**
     * @param string $tableName
     *
     * @return bool
     */
    private function checkTableExists($tableName)
    {
        $databaseName = $this->getContainer()->get('models')->getConnection()->getDatabase();

        $sql = "SELECT EXISTS (
            SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$databaseName' AND TABLE_NAME = '$tableName'
        );";

        return (bool) $this->getContainer()->get('dbal_connection')->fetchColumn($sql);
    }

    /**
     * @return void
     */
    private function resetDatabase()
    {
        $sql = file_get_contents(__DIR__ . '/../../../Setup/Assets/tables.sql');

        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->query($sql);
    }

    /**
     * @return Uninstaller
     */
    private function createUninstaller()
    {
        return new Uninstaller(
            $this->getContainer()->get('shopware_attribute.crud_service'),
            $this->getContainer()->get('models'),
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('paypal_unified.payment_method_provider')
        );
    }
}
