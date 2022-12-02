<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use Doctrine\DBAL\Connection;
use PDO;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Setup\ColumnService;
use SwagPaymentPayPalUnified\Setup\Versions\UpdateTo600;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseHelperTrait;

class UpdateTo600Test extends TestCase
{
    use ContainerTrait;
    use DatabaseHelperTrait;

    /**
     * @return void
     */
    public function testUpdate()
    {
        $connection = $this->getContainer()->get('dbal_connection');
        $this->importDemoData();
        $this->prepareUpdateTest($connection);

        $attributeCrudService = $this->getContainer()->get('shopware_attribute.crud_service');

        $attributeCrudService->delete('s_order_attributes', 'swag_paypal_unified_carrier_was_sent', true);
        $attributeCrudService->delete('s_order_attributes', 'swag_paypal_unified_carrier', true);
        $attributeCrudService->delete('s_premium_dispatch_attributes', 'swag_paypal_unified_carrier', true);

        $updater = new UpdateTo600($connection, $attributeCrudService, new ColumnService($connection));
        $updater->update();
        $updater->update();

        static::assertNotNull($attributeCrudService->get('s_order_attributes', 'swag_paypal_unified_carrier_was_sent'));
        static::assertNotNull($attributeCrudService->get('s_order_attributes', 'swag_paypal_unified_carrier'));
        static::assertNotNull($attributeCrudService->get('s_premium_dispatch_attributes', 'swag_paypal_unified_carrier'));

        $buttonLocals = $connection->query('SELECT shop_id, button_locale from swag_payment_paypal_unified_settings_general ORDER BY shop_id DESC')->fetchAll(PDO::FETCH_KEY_PAIR);

        static::assertEquals('en_US', $buttonLocals[2]);
        static::assertEquals('fr_XC', $buttonLocals[3]);

        static::assertTrue(
            $this->checkIfColumnExist($connection, 'swag_payment_paypal_unified_settings_installments', 'show_pay_later_paypal'),
            'Column show_pay_later_paypal is not created.'
        );

        static::assertTrue(
            $this->checkIfColumnExist($connection, 'swag_payment_paypal_unified_settings_installments', 'show_pay_later_express'),
            'Column show_pay_later_express is not created.'
        );

        $this->removeData();
    }

    /**
     * @return void
     */
    private function prepareUpdateTest(Connection $connection)
    {
        // Check if columns still there
        static::assertTrue(
            $this->checkIfColumnExist($connection, 'swag_payment_paypal_unified_settings_installments', 'show_pay_later_paypal'),
            'While installation the column show_pay_later_paypal was not created.'
        );

        static::assertTrue(
            $this->checkIfColumnExist($connection, 'swag_payment_paypal_unified_settings_installments', 'show_pay_later_express'),
            'While installation the column show_pay_later_express was not created.'
        );

        // Drop show_pay_later_paypal
        $payPalSql = 'ALTER TABLE `swag_payment_paypal_unified_settings_installments`
                DROP COLUMN `show_pay_later_paypal`';

        $connection->exec($payPalSql);

        static::assertFalse(
            $this->checkIfColumnExist($connection, 'swag_payment_paypal_unified_settings_installments', 'show_pay_later_paypal'),
            'Column show_pay_later_paypal is not dropped.'
        );

        // Drop show_pay_later_express
        $expressSql = 'ALTER TABLE `swag_payment_paypal_unified_settings_installments`
                DROP COLUMN `show_pay_later_express`';

        $connection->exec($expressSql);

        static::assertFalse(
            $this->checkIfColumnExist($connection, 'swag_payment_paypal_unified_settings_installments', 'show_pay_later_express'),
            'Column show_pay_later_express is not dropped.'
        );
    }

    /**
     * @return void
     */
    private function importDemoData()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/create_test_data.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);
    }

    /**
     * @return void
     */
    private function removeData()
    {
        $this->getContainer()->get('dbal_connection')->exec('
          TRUNCATE TABLE swag_payment_paypal_unified_settings_express;
          TRUNCATE TABLE swag_payment_paypal_unified_settings_general;
        ');
    }
}
