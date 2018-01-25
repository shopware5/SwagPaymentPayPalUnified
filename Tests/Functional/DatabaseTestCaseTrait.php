<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

trait DatabaseTestCaseTrait
{
    /**
     * @before
     */
    public function startTransactionBefore()
    {
        /** @var \Doctrine\DBAL\Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $dbalConnection->beginTransaction();
    }

    /**
     * @after
     */
    public function rollbackTransactionAfter()
    {
        /** @var \Doctrine\DBAL\Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $dbalConnection->rollBack();
    }
}
