<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ForwardCompatibility\Result;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConnectionMock extends TestCase
{
    const METHOD_FETCH = 'fetch';
    const METHOD_FETCH_ALL = 'fetchAll';
    const METHOD_FETCH_COLUMN = 'fetchColumn';
    const METHOD_FETCH_ASSOCIATIVE = 'fetchAssociative';
    const METHOD_FETCH_ONE = 'fetchOne';

    /**
     * @param string|bool|mixed $returnValue
     * @param string            $method
     *
     * @return Connection&MockObject
     */
    public function createConnectionMock($returnValue, $method)
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);

        if (class_exists(Result::class)) {
            $forwardCompatibilityResultMock = $this->createMock(Result::class);
            $forwardCompatibilityResultMock->method($method)->willReturn($returnValue);
            $queryBuilderMock->method('distinct')->willReturnSelf();
        } else {
            $forwardCompatibilityResultMock = $this->createMock(Statement::class);
            $forwardCompatibilityResultMock->method($method)->willReturn($returnValue);
        }

        $queryBuilderMock->method('expr')->willReturnSelf();
        $queryBuilderMock->method('setParameter')->willReturnSelf();
        $queryBuilderMock->method('setParameters')->willReturnSelf();
        $queryBuilderMock->method('setFirstResult')->willReturnSelf();
        $queryBuilderMock->method('setMaxResults')->willReturnSelf();
        $queryBuilderMock->method('add')->willReturnSelf();
        $queryBuilderMock->method('select')->willReturnSelf();
        $queryBuilderMock->method('addSelect')->willReturnSelf();
        $queryBuilderMock->method('delete')->willReturnSelf();
        $queryBuilderMock->method('update')->willReturnSelf();
        $queryBuilderMock->method('insert')->willReturnSelf();
        $queryBuilderMock->method('from')->willReturnSelf();
        $queryBuilderMock->method('join')->willReturnSelf();
        $queryBuilderMock->method('innerJoin')->willReturnSelf();
        $queryBuilderMock->method('leftJoin')->willReturnSelf();
        $queryBuilderMock->method('rightJoin')->willReturnSelf();
        $queryBuilderMock->method('set')->willReturnSelf();
        $queryBuilderMock->method('where')->willReturnSelf();
        $queryBuilderMock->method('andWhere')->willReturnSelf();
        $queryBuilderMock->method('orWhere')->willReturnSelf();
        $queryBuilderMock->method('groupBy')->willReturnSelf();
        $queryBuilderMock->method('addGroupBy')->willReturnSelf();
        $queryBuilderMock->method('setValue')->willReturnSelf();
        $queryBuilderMock->method('values')->willReturnSelf();
        $queryBuilderMock->method('having')->willReturnSelf();
        $queryBuilderMock->method('andHaving')->willReturnSelf();
        $queryBuilderMock->method('orHaving')->willReturnSelf();
        $queryBuilderMock->method('orderBy')->willReturnSelf();
        $queryBuilderMock->method('addOrderBy')->willReturnSelf();
        $queryBuilderMock->method('resetQueryParts')->willReturnSelf();
        $queryBuilderMock->method('resetQueryPart')->willReturnSelf();

        $queryBuilderMock->method('execute')->willReturn($forwardCompatibilityResultMock);

        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->method('createQueryBuilder')->willReturn($queryBuilderMock);

        return $connectionMock;
    }
}
