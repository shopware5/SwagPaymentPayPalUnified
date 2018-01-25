<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

use Doctrine\DBAL\Query\QueryBuilder;

trait PayPalUnifiedPaymentIdTrait
{
    /**
     * @return bool|string
     */
    protected function getUnifiedPaymentId()
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();

        return $queryBuilder->select('id')
            ->from('s_core_paymentmeans')
            ->where('name = :name')
            ->setParameter(':name', 'SwagPaymentPayPalUnified')
            ->execute()
            ->fetchColumn();
    }

    protected function getInstallmentsPaymentId()
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();

        return $queryBuilder->select('id')
            ->from('s_core_paymentmeans')
            ->where('name = :name')
            ->setParameter(':name', 'SwagPaymentPayPalUnifiedInstallments')
            ->execute()
            ->fetchColumn();
    }
}
