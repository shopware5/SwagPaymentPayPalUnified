<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Setup\Versions\UpdateTo504;
use SwagPaymentPayPalUnified\Tests\Functional\AssertStringContainsTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class UpdateTo504Test extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use AssertStringContainsTrait;

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

        $updater = new UpdateTo504($connection);
        $updater->update();

        $expectedContainedString = 'Bitte überweisen Sie {$PayPalUnifiedInvoiceInstruction.amount|currency} bis {$PayPalUnifiedInvoiceInstruction.dueDate|date_format: "%d.%m.%Y"} an:';
        $result = $connection->fetchColumn($fetchValueSql);

        static::assertStringContains($this, $expectedContainedString, $result);
    }
}
