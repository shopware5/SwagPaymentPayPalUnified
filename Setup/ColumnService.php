<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup;

use Doctrine\DBAL\Connection;

class ColumnService
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Helper function to check if a column exists which is needed during update
     *
     * @param string $tableName
     * @param string $columnName
     *
     * @return bool
     */
    public function checkIfColumnExist($tableName, $columnName)
    {
        $sql = 'SELECT column_name
                FROM information_schema.columns
                WHERE table_name = :tableName
                    AND column_name = :columnName
                    AND table_schema = DATABASE();';

        $columnNameInDb = $this->connection->executeQuery(
            $sql,
            ['tableName' => $tableName, 'columnName' => $columnName]
        )->fetchColumn();

        return $columnNameInDb === $columnName;
    }
}
