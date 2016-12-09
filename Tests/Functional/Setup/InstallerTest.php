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

use Doctrine\ORM\EntityManager;
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

        /** @var EntityManager $em */
        $em = Shopware()->Container()->get('models');

        $em->persist($entity);
        $em->flush($entity);

        $installer = new Installer($em);
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

        /** @var EntityManager $em */
        $em = Shopware()->Container()->get('models');

        $em->persist($entity);
        $em->flush($entity);

        $installer = new Installer($em);
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

        /** @var EntityManager $em */
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

        $installer = new Installer($em);
        $this->expectException(InstallationException::class);
        $installer->install(new InstallContext($this->getPluginModel(), Kernel::VERSION, '1.0.0'));
    }

    public function test_installer_without_classic_installed()
    {
        /** @var EntityManager $em */
        $em = Shopware()->Container()->get('models');
        $installer = new Installer($em);

        $result = $installer->install(new InstallContext($this->getPluginModel(), Kernel::VERSION, '1.0.0'));
        $this->assertTrue($result);
    }

    private function getPluginModel()
    {
        /** @var EntityManager $em */
        $em = Shopware()->Container()->get('models');

        return $em->getRepository(Plugin::class)->findOneBy(['name' => 'SwagPaymentPayPalUnified']);
    }
}
