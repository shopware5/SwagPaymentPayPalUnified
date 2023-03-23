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
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\Setup\Versions\UpdateTo604;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class UpdateTo604Test extends TestCase
{
    use ContainerTrait;
    use SettingsHelperTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testUpdate()
    {
        $this->insertGeneralSettingsFromArray(['active' => 1]);
        $this->insertGeneralSettingsFromArray(['active' => 1, 'shop_id' => 2]);
        $this->insertExpressCheckoutSettingsFromArray([]);
        $this->insertExpressCheckoutSettingsFromArray(['shop_id' => 2]);

        $currentGeneralSize = $this->getButtonStyleSizeFromDatabase(SettingsTable::FULL[SettingsTable::GENERAL]);
        static::assertCount(2, $currentGeneralSize);
        $currentExpressSize = $this->getButtonStyleSizeFromDatabase(SettingsTable::FULL[SettingsTable::EXPRESS_CHECKOUT]);
        static::assertCount(2, $currentExpressSize);

        foreach ($currentGeneralSize as $currentSize) {
            static::assertSame('medium', $currentSize);
        }
        foreach ($currentExpressSize as $currentSize) {
            static::assertSame('medium', $currentSize);
        }

        $update = $this->createUpdateTo604();
        $update->update();
        $update->update();

        $generalSizeResult = $this->getButtonStyleSizeFromDatabase(SettingsTable::FULL[SettingsTable::GENERAL]);
        static::assertCount(2, $generalSizeResult);
        $expressSizeResult = $this->getButtonStyleSizeFromDatabase(SettingsTable::FULL[SettingsTable::EXPRESS_CHECKOUT]);
        static::assertCount(2, $expressSizeResult);

        foreach ($generalSizeResult as $currentSize) {
            static::assertSame('responsive', $currentSize);
        }
        foreach ($expressSizeResult as $currentSize) {
            static::assertSame('responsive', $currentSize);
        }
    }

    /**
     * @param string $table
     *
     * @return array<int,mixed>
     */
    private function getButtonStyleSizeFromDatabase($table)
    {
        return $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->select(['button_style_size'])
            ->from($table)
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return UpdateTo604
     */
    private function createUpdateTo604()
    {
        return new UpdateTo604($this->getContainer()->get('dbal_connection'));
    }
}
