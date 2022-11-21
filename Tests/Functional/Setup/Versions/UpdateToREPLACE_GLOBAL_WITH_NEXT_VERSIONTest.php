<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use PDO;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Setup\Versions\UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSION;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;

class UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSIONTest extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testUpdate()
    {
        $connection = $this->getContainer()->get('dbal_connection');
        $this->importDemoData();

        $attributeCrudService = $this->getContainer()->get('shopware_attribute.crud_service');

        $attributeCrudService->delete('s_order_attributes', 'swag_paypal_unified_carrier_was_sent', true);
        $attributeCrudService->delete('s_order_attributes', 'swag_paypal_unified_carrier', true);
        $attributeCrudService->delete('s_premium_dispatch_attributes', 'swag_paypal_unified_carrier', true);

        $updater = new UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSION($attributeCrudService, $connection);
        $updater->update();

        static::assertNotNull($attributeCrudService->get('s_order_attributes', 'swag_paypal_unified_carrier_was_sent'));
        static::assertNotNull($attributeCrudService->get('s_order_attributes', 'swag_paypal_unified_carrier'));
        static::assertNotNull($attributeCrudService->get('s_premium_dispatch_attributes', 'swag_paypal_unified_carrier'));

        $buttonLocals = $connection->query('SELECT shop_id, button_locale from swag_payment_paypal_unified_settings_general ORDER BY shop_id DESC')->fetchAll(PDO::FETCH_KEY_PAIR);

        static::assertEquals('en_US', $buttonLocals[2]);
        static::assertEquals('fr_XC', $buttonLocals[3]);

        $this->removeData();
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
