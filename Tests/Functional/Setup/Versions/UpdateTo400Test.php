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
use ReflectionClass;
use Shopware\Models\Plugin\Plugin;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\ApplicationContext;
use SwagPaymentPayPalUnified\Setup\ColumnService;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModelFactory;
use SwagPaymentPayPalUnified\Setup\Versions\UpdateTo400;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class UpdateTo400Test extends TestCase
{
    use ContainerTrait;
    use SettingsHelperTrait;

    /**
     * @return void
     */
    public function testMigrateLandingPageType()
    {
        $this->getContainer()->get('dbal_connection')->beginTransaction();

        $expectedResult = [
            ApplicationContext::LANDING_PAGE_TYPE_LOGIN,
            ApplicationContext::LANDING_PAGE_TYPE_BILLING,
            ApplicationContext::LANDING_PAGE_TYPE_LOGIN,
            ApplicationContext::LANDING_PAGE_TYPE_BILLING,
            'FOO',
        ];

        foreach ($expectedResult as $index => $landingPageType) {
            $this->insertGeneralSettingsFromArray(['shopId' => $index, 'landingPageType' => ucfirst(strtolower($landingPageType))]);
        }

        $reflectionMethod = (new ReflectionClass(UpdateTo400::class))
            ->getMethod('migrateLandingPageType');
        $reflectionMethod->setAccessible(true);

        $updater = $this->createUpdater();
        $reflectionMethod->invoke($updater);

        $result = $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->select('landing_page_type')
            ->from('swag_payment_paypal_unified_settings_general')
            ->orderBy('id')
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);

        foreach ($result as $index => $value) {
            static::assertSame($expectedResult[$index], $value);
        }

        $this->getContainer()->get('dbal_connection')->rollBack();
    }

    /**
     * @return void
     */
    public function testHasShopIdColumnUniqueIndex()
    {
        $this->installTestTables();

        $tables = $this->getTestTables();

        $reflectionMethod = (new ReflectionClass(UpdateTo400::class))
            ->getMethod('hasShopIdColumnUniqueIndex');
        $reflectionMethod->setAccessible(true);

        $updater = $this->createUpdater();

        $resultOne = $reflectionMethod->invokeArgs($updater, [$tables[0]]);
        $resultTwo = $reflectionMethod->invokeArgs($updater, [$tables[1]]);
        $resultThree = $reflectionMethod->invokeArgs($updater, [$tables[2]]);
        $resultFour = $reflectionMethod->invokeArgs($updater, [$tables[3]]);

        static::assertTrue($resultOne);
        static::assertTrue($resultTwo);
        static::assertFalse($resultThree);
        static::assertFalse($resultFour);

        $this->dropTestTables();
    }

    /**
     * @return void
     */
    public function testMakeShopIdUnique()
    {
        $this->installTestTables();

        $tables = $this->getTestTables();

        $reflectionMethodMakeShopIdUnique = (new ReflectionClass(UpdateTo400::class))
            ->getMethod('makeShopIdUnique');
        $reflectionMethodMakeShopIdUnique->setAccessible(true);

        $updater = $this->createUpdater();

        $reflectionMethodMakeShopIdUnique->invokeArgs($updater, [$tables]);

        $reflectionMethodHasShopIdColumnUniqueIndex = (new ReflectionClass(UpdateTo400::class))
            ->getMethod('hasShopIdColumnUniqueIndex');
        $reflectionMethodHasShopIdColumnUniqueIndex->setAccessible(true);

        foreach ($tables as $testTableName) {
            static::assertTrue($reflectionMethodHasShopIdColumnUniqueIndex->invokeArgs($updater, [$testTableName]), $testTableName);
        }

        $this->dropTestTables();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     *
     * @return void
     */
    private function installTestTables()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/install_test_tables.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     *
     * @return void
     */
    private function dropTestTables()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/drop_test_tables.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);
    }

    /**
     * @return array<int, string>
     */
    private function getTestTables()
    {
        return [
            'swag_payment_paypal_unified_php_unit_test_table_one',
            'swag_payment_paypal_unified_php_unit_test_table_two',
            'swag_payment_paypal_unified_php_unit_test_table_three',
            'swag_payment_paypal_unified_php_unit_test_table_four',
        ];
    }

    /**
     * @return UpdateTo400
     */
    private function createUpdater()
    {
        return new UpdateTo400(
            $this->getContainer()->get('models'),
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('paypal_unified.payment_method_provider'),
            $this->createPaymentModelFactory(),
            $this->createColumnService()
        );
    }

    /**
     * @return PaymentModelFactory
     */
    private function createPaymentModelFactory()
    {
        $plugin = $this->getContainer()->get('models')
            ->getRepository(Plugin::class)
            ->findOneBy(['name' => 'SwagPaymentPayPalUnified']);

        if (!$plugin instanceof Plugin) {
            throw new \UnexpectedValueException('Plugin SwagPaymentPayPalUnified not found');
        }

        return new PaymentModelFactory($plugin);
    }

    /**
     * @return ColumnService
     */
    private function createColumnService()
    {
        return new ColumnService($this->getContainer()->get('dbal_connection'));
    }
}
