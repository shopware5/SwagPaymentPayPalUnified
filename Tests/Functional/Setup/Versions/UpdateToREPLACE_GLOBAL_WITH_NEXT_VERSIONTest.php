<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Setup\Versions\UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSION;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSIONTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testUpdate()
    {
        $changeTemplateSql = "UPDATE s_core_documents_box SET value = 'AnyOtherTemplate' WHERE `name` = 'PayPal_Unified_Ratepay_Instructions';";
        $fetchValueSql = 'SELECT value FROM s_core_documents_box WHERE `name` = "PayPal_Unified_Ratepay_Instructions";';

        $connection = $this->getContainer()->get('dbal_connection');

        $connection->executeQuery($changeTemplateSql);

        // make sure the value before the update is another.
        static::assertSame('AnyOtherTemplate', $connection->fetchColumn($fetchValueSql));

        $updater = new UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSION($connection);
        $updater->update();

        $expectedContainedString = 'Bitte überweisen Sie den Betrag innerhalb der nächsten 30 Tage.';
        $result = $connection->fetchColumn($fetchValueSql);

        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString($expectedContainedString, $result);

            return;
        }

        static::assertContains($expectedContainedString, $result);
    }
}
