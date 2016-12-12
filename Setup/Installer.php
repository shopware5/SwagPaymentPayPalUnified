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

namespace SwagPaymentPayPalUnified\Setup;

use Doctrine\ORM\EntityManager;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Plugin\Plugin;

class Installer
{

    /** @var EntityManager $kernel */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param InstallContext $installContext
     * @return bool
     * @throws InstallationException
     */
    public function install(InstallContext $installContext)
    {
        if ($this->hasPayPalClassicInstalled()) {
            throw new InstallationException('This plugin can not be used while PayPal Classic or PayPal Plus are installed and active.');
        }

        $this->createPaymentMethod();

        return true;
    }

    /**
     * @return bool
     */
    private function hasPayPalClassicInstalled()
    {
        $classicPlugin = $this->entityManager->getRepository(Plugin::class)->findOneBy([
            'name' => 'SwagPaymentPaypal',
            'active' => 1
        ]);
        $classicPlusPlugin = $this->entityManager->getRepository(Plugin::class)->findOneBy([
            'name' => 'SwagPaymentPaypalPlus',
            'active' => 1
        ]);

        return $classicPlugin != null || $classicPlusPlugin != null;
    }

    private function createPaymentMethod()
    {
        $existingPayment = $this->entityManager->getRepository(Payment::class)->findOneBy([
            'name' => 'SwagPaymentPayPalUnified'
        ]);

        if ($existingPayment !== null) {
            //If the payment does already exist, we don't need to add it again.
            return;
        }

        $entity = new Payment();
        $entity->setActive(false);
        $entity->setName('SwagPaymentPayPalUnified');
        $entity->setAdditionalDescription('<div id="ppplus"></div>'); //This is the placeholder for the iframe
        $entity->setDescription('PayPal');
        $entity->setAction('PayPal');

        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
    }
}
