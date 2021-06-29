<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\RiskManagement;

use Doctrine\DBAL\Connection;

class EsdProductChecker implements EsdProductCheckerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function checkForEsdProducts(array $productIds)
    {
        $result = $this->connection->createQueryBuilder()
            ->select(['id'])
            ->from('s_articles_esd')
            ->where('articleID IN (:productIds)')
            ->setParameter('productIds', $productIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll();

        return !empty($result);
    }

    public function getEsdProductNumbers($categoryId)
    {
        return $this->connection->createQueryBuilder()
            ->select('details.ordernumber')
            ->from('s_articles_details', 'details')
            ->join('details', 's_articles_esd', 'esd', 'details.articleID = esd.articleID')
            ->join('details', 's_articles_categories_ro', 'categoryRelation', 'details.articleID = categoryRelation.articleID')
            ->where('categoryRelation.categoryID = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
