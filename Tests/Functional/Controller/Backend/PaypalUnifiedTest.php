<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Backend;

use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;

class PaypalUnifiedTest extends ControllerTestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testIndexAction()
    {
        $this->getContainer()->get('dbal_connection')->executeUpdate("UPDATE `s_core_shops` SET `base_url` = '/de' WHERE `s_core_shops`.`id` = 1;");

        // disable auth and acl
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAuth();
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAcl();

        $this->Request()->setParam('file', 'app');
        $responseText = $this->dispatch('/backend/paypalUnified')->getBody();
        static::assertTrue(\is_string($responseText));

        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString("loadPath: '/backend/paypalUnified/load',", $responseText);
        } else {
            static::assertContains("loadPath: '/backend/paypalUnified/load',", $responseText);
        }

        $this->getContainer()->get('dbal_connection')->executeUpdate('UPDATE `s_core_shops` SET `base_url` = NULL WHERE `s_core_shops`.`id` = 1;');
    }
}
