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

class UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSION_Test extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testUpdate()
    {
        $this->prepareTestCase();

        $this->getUpdater()->update();

        $result = $this->getResultFromDatabase();

        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('{if $PayPalUnifiedInvoiceInstruction}', $result);

            return;
        }

        static::assertContains('{if $PayPalUnifiedInvoiceInstruction}', $result);
    }

    /**
     * @return string
     */
    private function getResultFromDatabase()
    {
        $sql = 'SELECT `value` FROM s_core_documents_box WHERE `name` = "PayPal_Unified_Ratepay_Instructions";';

        $result = $this->getContainer()->get('dbal_connection')->fetchColumn($sql);
        if (!\is_string($result)) {
            return '';
        }

        return $result;
    }

    /**
     * @return void
     */
    private function prepareTestCase()
    {
        $sql = "UPDATE s_core_documents_box SET value = '' WHERE `name` = 'PayPal_Unified_Ratepay_Instructions';";

        $this->getContainer()->get('dbal_connection')->exec($sql);

        $assurance = $this->getResultFromDatabase();

        static::assertEmpty($assurance);
    }

    /**
     * @return UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSION
     */
    private function getUpdater()
    {
        return new UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSION(
            $this->getContainer()->get('dbal_connection')
        );
    }
}
