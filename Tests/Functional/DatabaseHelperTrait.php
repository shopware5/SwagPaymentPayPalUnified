<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

use Doctrine\DBAL\Connection;

trait DatabaseHelperTrait
{
    /**
     * @param string $tableName
     * @param string $columnName
     *
     * @return bool
     */
    public function checkIfColumnExist(Connection $connection, $tableName, $columnName)
    {
        $sql = 'SELECT column_name
                FROM information_schema.columns
                WHERE table_name = :tableName
                    AND column_name = :columnName
                    AND table_schema = DATABASE();';

        $columnNameInDb = $connection->executeQuery(
            $sql,
            ['tableName' => $tableName, 'columnName' => $columnName]
        )->fetchColumn();

        return $columnNameInDb === $columnName;
    }

    /**
     * @param string $tableName
     *
     * @return bool
     */
    public function checkTableExists(Connection $connection, $tableName)
    {
        $databaseName = $connection->getDatabase();

        $sql = "SELECT EXISTS (
            SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$databaseName' AND TABLE_NAME = '$tableName'
        );";

        return (bool) $connection->fetchColumn($sql);
    }
}
