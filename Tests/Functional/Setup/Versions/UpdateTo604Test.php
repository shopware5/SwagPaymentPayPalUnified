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
use SwagPaymentPayPalUnified\Setup\TranslationUpdater;
use SwagPaymentPayPalUnified\Setup\Versions\UpdateTo604;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\TranslationTestCaseTrait;

class UpdateTo604Test extends TestCase
{
    use TranslationTestCaseTrait;
    use SettingsHelperTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testUpdate()
    {
        $sql = file_get_contents(__DIR__ . '/../../../_fixtures/shops_for_translation.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

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

        $translationReader = $this->getTranslationService();
        $paymentMethodId = 8;

        $australianTranslation = $translationReader->read(3, TranslationUpdater::TRANSLATION_TYPE, $paymentMethodId, true);
        static::assertArrayHasKey('description', $australianTranslation);
        static::assertSame('PayPal, Pay in 4', $australianTranslation['description']);

        $usTranslation = $translationReader->read(4, TranslationUpdater::TRANSLATION_TYPE, $paymentMethodId, true);
        static::assertArrayHasKey('description', $usTranslation);
        static::assertSame('PayPal, Pay Later', $usTranslation['description']);

        $spainTranslation = $translationReader->read(5, TranslationUpdater::TRANSLATION_TYPE, $paymentMethodId, true);
        static::assertArrayHasKey('description', $spainTranslation);
        static::assertSame('PayPal, Paga en 3 plazos', $spainTranslation['description']);

        $frenchTranslation = $translationReader->read(6, TranslationUpdater::TRANSLATION_TYPE, $paymentMethodId, true);
        static::assertArrayHasKey('description', $frenchTranslation);
        static::assertSame('PayPal, Paiement en 4X', $frenchTranslation['description']);

        $italianTranslation = $translationReader->read(7, TranslationUpdater::TRANSLATION_TYPE, $paymentMethodId, true);
        static::assertArrayHasKey('description', $italianTranslation);
        static::assertSame('PayPal, Paga in 3 rate', $italianTranslation['description']);
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
        return new UpdateTo604(
            $this->getContainer()->get('dbal_connection'),
            $this->getTranslationService()
        );
    }
}
