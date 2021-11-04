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
     * @var bool;
     */
    protected $shouldRollback = true;

    /**
     * @before
     */
    public function startTransactionBefore()
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $dbalConnection->beginTransaction();
    }

    /**
     * @after
     */
    public function rollbackTransactionAfter()
    {
        if ($this->shouldRollback === false) {
            $this->shouldRollback = true;

            return;
        }

        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $dbalConnection->rollBack();
    }
}
