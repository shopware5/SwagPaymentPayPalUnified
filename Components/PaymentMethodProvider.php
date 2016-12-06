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

namespace SwagPaymentPayPalUnified\Components;

use Doctrine\ORM\EntityManager;
use Shopware\Models\Payment\Payment;

class PaymentMethodProvider
{
    /** @var EntityManager $entityManager */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return null|Payment
     */
    public function getPaymentMethodModel()
    {
        return $this->entityManager->getRepository(Payment::class)->findOneBy([
            'name' => 'SwagPaymentPayPalUnified'
        ]);
    }

    /**
     * @param bool $active
     */
    public function setPaymentMethodActive($active)
    {
        $paymentMethod = $this->getPaymentMethodModel();
        $paymentMethod->setActive($active);

        $this->entityManager->persist($paymentMethod);
        $this->entityManager->flush($paymentMethod);
    }
}