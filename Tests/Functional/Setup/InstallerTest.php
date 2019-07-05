<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Plugin\Plugin;
use SwagPaymentPayPalUnified\Setup\InstallationException;
use SwagPaymentPayPalUnified\Setup\Installer;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class InstallerTest extends TestCase
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

        $container = Shopware()->Container();

        /** @var ModelManager $em */
        $em = $container->get('models');
        $em->persist($entity);
        $em->flush($entity);

        $installer = new Installer(
            $em,
            $container->get('dbal_connection'),
            $container->get('shopware_attribute.crud_service'),
            $this->getTranslationService(),
            $container->getParameter('paypal_unified.plugin_dir')
        );
        $this->expectException(InstallationException::class);
        $installer->install();
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

        $container = Shopware()->Container();

        /** @var ModelManager $em */
        $em = $container->get('models');

        $em->persist($entity);
        $em->flush($entity);

        $installer = new Installer(
            $em,
            $container->get('dbal_connection'),
            $container->get('shopware_attribute.crud_service'),
            $this->getTranslationService(),
            $container->getParameter('paypal_unified.plugin_dir')
        );
        $this->expectException(InstallationException::class);
        $installer->install();
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

        $container = Shopware()->Container();

        /** @var ModelManager $em */
        $em = $container->get('models');

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
            $container->get('dbal_connection'),
            $container->get('shopware_attribute.crud_service'),
            $this->getTranslationService(),
            $container->getParameter('paypal_unified.plugin_dir')
        );
        $this->expectException(InstallationException::class);
        $installer->install();
    }

    public function test_installer_without_classic_installed()
    {
        /** @var ModelManager $em */
        $em = Shopware()->Container()->get('models');

        $container = Shopware()->Container();

        $installer = new Installer(
            $em,
            $container->get('dbal_connection'),
            $container->get('shopware_attribute.crud_service'),
            $this->getTranslationService(),
            $container->getParameter('paypal_unified.plugin_dir')
        );

        $result = $installer->install();
        static::assertTrue($result);
    }

    public function test_order_attribute_available()
    {
        $query = "SELECT 1 
                    FROM information_schema.COLUMNS 
                    WHERE TABLE_NAME = 's_order_attributes' 
                    AND COLUMN_NAME = 'swag_paypal_unified_payment_type'";

        /** @var Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');
        $columnAvailable = (bool) $connection->executeQuery($query)->fetch(\PDO::FETCH_COLUMN);

        static::assertTrue($columnAvailable);
    }

    public function test_instructions_table_exists()
    {
        $query = "SHOW TABLES LIKE 'swag_payment_paypal_unified_payment_instruction'";

        static::assertCount(1, Shopware()->Db()->fetchAll($query));
    }

    public function test_document_footer_template_exists()
    {
        $query = "SELECT id FROM s_core_documents_box WHERE `name` = 'PayPal_Unified_Instructions_Footer'";

        static::assertCount(1, Shopware()->Db()->fetchRow($query));
    }

    public function test_document_content_template_exists()
    {
        $query = "SELECT id FROM s_core_documents_box WHERE `name` = 'PayPal_Unified_Instructions_Content'";

        static::assertCount(1, Shopware()->Db()->fetchRow($query));
    }

    public function test_settings_tables_exists()
    {
        $query = "SHOW TABLES LIKE 'swag_payment_paypal_unified_settings_express';";
        static::assertCount(1, Shopware()->Db()->fetchAll($query));

        $query = "SHOW TABLES LIKE 'swag_payment_paypal_unified_settings_installments';";
        static::assertCount(1, Shopware()->Db()->fetchAll($query));

        $query = "SHOW TABLES LIKE 'swag_payment_paypal_unified_settings_plus';";
        static::assertCount(1, Shopware()->Db()->fetchAll($query));

        $query = "SHOW TABLES LIKE 'swag_payment_paypal_unified_settings_general';";
        static::assertCount(1, Shopware()->Db()->fetchAll($query));
    }

    /**
     * @return \Shopware_Components_Translation
     */
    private function getTranslationService()
    {
        $container = Shopware()->Container();

        if ($container->has('translation')) {
            return $container->get('translation');
        }

        // for Shopware versions before 5.6
        return new \Shopware_Components_Translation();
    }
}
