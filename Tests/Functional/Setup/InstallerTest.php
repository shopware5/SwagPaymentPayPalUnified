<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Kernel;
use Shopware\Models\Plugin\Plugin;
use SwagPaymentPayPalUnified\Setup\InstallationException;
use SwagPaymentPayPalUnified\Setup\Installer;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class InstallerTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;

    public function test_installer_with_classic_installed()
    {
        $entity = new Plugin();
        $entity->setActive(true);
        $entity->setName('SwagPaymentPaypal');
        $entity->setLabel('PayPal');
        $entity->setNamespace('Frontend');
        $entity->setSource('Community');
        $entity->setVersion('1.0.0');

        /** @var ModelManager $em */
        $em = Shopware()->Container()->get('models');

        $em->persist($entity);
        $em->flush($entity);

        $installer = new Installer(
            $em,
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('shopware_attribute.crud_service'),
            Shopware()->Container()->getParameter('paypal_unified.plugin_dir')
        );
        $this->expectException(InstallationException::class);
        $installer->install(new InstallContext($this->getPluginModel(), Kernel::VERSION, '1.0.0'));
    }

    public function test_installer_with_plus_installed()
    {
        $entity = new Plugin();
        $entity->setActive(true);
        $entity->setName('SwagPaymentPaypalPlus');
        $entity->setLabel('PayPal Plus');
        $entity->setNamespace('Frontend');
        $entity->setSource('Community');
        $entity->setVersion('1.0.0');

        /** @var ModelManager $em */
        $em = Shopware()->Container()->get('models');

        $em->persist($entity);
        $em->flush($entity);

        $installer = new Installer(
            $em,
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('shopware_attribute.crud_service'),
            Shopware()->Container()->getParameter('paypal_unified.plugin_dir')
        );
        $this->expectException(InstallationException::class);
        $installer->install(new InstallContext($this->getPluginModel(), Kernel::VERSION, '1.0.0'));
    }

    public function test_installer_with_both_installed()
    {
        $entity = new Plugin();
        $entity->setActive(true);
        $entity->setName('SwagPaymentPaypalPlus');
        $entity->setLabel('PayPal Plus');
        $entity->setNamespace('Frontend');
        $entity->setSource('Community');
        $entity->setVersion('1.0.0');

        /** @var ModelManager $em */
        $em = Shopware()->Container()->get('models');

        $em->persist($entity);
        $em->flush($entity);

        $entity = new Plugin();
        $entity->setActive(true);
        $entity->setName('SwagPaymentPaypal');
        $entity->setLabel('PayPal');
        $entity->setNamespace('Frontend');
        $entity->setSource('Community');
        $entity->setVersion('1.0.0');
        $em->persist($entity);
        $em->flush($entity);

        $installer = new Installer(
            $em,
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('shopware_attribute.crud_service'),
            Shopware()->Container()->getParameter('paypal_unified.plugin_dir')
        );
        $this->expectException(InstallationException::class);
        $installer->install(new InstallContext($this->getPluginModel(), Kernel::VERSION, '1.0.0'));
    }

    public function test_installer_without_classic_installed()
    {
        /** @var ModelManager $em */
        $em = Shopware()->Container()->get('models');
        $installer = new Installer(
            $em,
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('shopware_attribute.crud_service'),
            Shopware()->Container()->getParameter('paypal_unified.plugin_dir')
        );

        $result = $installer->install(new InstallContext($this->getPluginModel(), Kernel::VERSION, '1.0.0'));
        $this->assertTrue($result);
    }

    public function test_order_attribute_available()
    {
        $query = "SELECT * 
                    FROM information_schema.COLUMNS 
                    WHERE TABLE_NAME = 's_order_attributes' 
                    AND COLUMN_NAME = 'paypal_payment_type'";

        $this->assertCount(2, Shopware()->Db()->fetchCol($query));
    }

    public function test_instructions_table_exists()
    {
        $query = "SHOW TABLES LIKE 'swag_payment_paypal_unified_payment_instruction'";

        $this->assertCount(1, Shopware()->Db()->fetchAll($query));
    }

    public function test_document_footer_template_exists()
    {
        $query = "SELECT id FROM s_core_documents_box WHERE `name` = 'PayPal_Unified_Instructions_Footer'";

        $this->assertCount(1, Shopware()->Db()->fetchRow($query));
    }

    public function test_document_content_template_exists()
    {
        $query = "SELECT id FROM s_core_documents_box WHERE `name` = 'PayPal_Unified_Instructions_Content'";

        $this->assertCount(1, Shopware()->Db()->fetchRow($query));
    }

    private function getPluginModel()
    {
        /** @var ModelManager $em */
        $em = Shopware()->Container()->get('models');

        return $em->getRepository(Plugin::class)->findOneBy(['name' => 'SwagPaymentPayPalUnified']);
    }
}
