<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\RiskManagement;

use Doctrine\DBAL\Connection;
use SwagPaymentPayPalUnified\Components\DependencyProvider;

class RiskManagementHelper implements RiskManagementHelperInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    public function __construct(Connection $connection, DependencyProvider $dependencyProvider)
    {
        $this->connection = $connection;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * @param string|null $attributeRule
     *
     * @return Attribute
     */
    public function createAttribute($attributeRule = null)
    {
        $attributeRuleArray = [];
        if ($attributeRule !== null) {
            $attributeRuleArray = \explode('|', $attributeRule);
        }

        return new Attribute($attributeRuleArray);
    }

    /**
     * @param int $eventCategoryId
     *
     * @return Context
     */
    public function createContext(Attribute $attribute, $eventCategoryId = null)
    {
        $session = $this->dependencyProvider->getSession();

        return new Context(
            $attribute,
            $session->offsetGet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME),
            $session->offsetGet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME),
            $eventCategoryId
        );
    }

    /**
     * @return bool
     */
    public function isProductInCategory(Context $context)
    {
        return (bool) $this->connection->createQueryBuilder()
            ->select('articlesCategory.id')
            ->from('s_articles_categories_ro', 'articlesCategory')
            ->where('articlesCategory.articleID = :productId')
            ->andWhere('articlesCategory.categoryID = :categoryId')
            ->setParameter('productId', $context->getSessionProductId())
            ->setParameter('categoryId', $context->getEventCategoryId())
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return bool
     */
    public function isCategoryAmongTheParents(Context $context)
    {
        $path = $this->connection->createQueryBuilder()
            ->select('category.path')
            ->from('s_categories', 'category')
            ->where('category.id = :categoryId')
            ->setParameter('categoryId', $context->getSessionCategoryId())
            ->execute()
            ->fetchColumn();

        return \in_array($context->getEventCategoryId(), \explode('|', $path), false);
    }

    /**
     * @return bool
     */
    public function hasProductAttributeValue(Context $context)
    {
        if (!$context->getAttribute()->isValid()) {
            return false;
        }

        return (bool) $this->connection->createQueryBuilder()
            ->select('attributes.id')
            ->from('s_articles_attributes', 'attributes')
            ->join('attributes', 's_articles_details', 'details', 'attributes.articledetailsID  = details.id')
            ->where('details.articleID = :productId')
            ->andWhere(\sprintf('attributes.%s = :attributeValue', $context->getAttribute()->getAttributeName()))
            ->andWhere('details.active = 1')
            ->setParameter('productId', $context->getSessionProductId())
            ->setParameter('attributeValue', $context->getAttribute()->getAttributeValue())
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return array
     */
    public function getProductOrdernumbersMatchedAttribute(Context $context)
    {
        if (!$context->getAttribute()->isValid()) {
            return [];
        }

        $result = $this->getProductsInCategory($context);

        return $this->connection->createQueryBuilder()
            ->select('details.ordernumber')
            ->from('s_articles_attributes', 'attributes')
            ->join('attributes', 's_articles_details', 'details', 'attributes.articledetailsID  = details.id')
            ->where('details.articleID IN (:productIds)')
            ->andWhere(\sprintf('attributes.%s = :attributeValue', $context->getAttribute()->getAttributeName()))
            ->andWhere('details.active = 1')
            ->setParameter('productIds', $result, Connection::PARAM_INT_ARRAY)
            ->setParameter('attributeValue', $context->getAttribute()->getAttributeValue())
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @return array
     */
    public function getProductOrdernumbersNotMatchedAttribute(Context $context)
    {
        if (!$context->getAttribute()->isValid()) {
            return [];
        }

        $result = $this->getProductsInCategory($context);

        return $this->connection->createQueryBuilder()
            ->select('details.ordernumber')
            ->from('s_articles_attributes', 'attributes')
            ->join('attributes', 's_articles_details', 'details', 'attributes.articledetailsID  = details.id')
            ->where('details.articleID IN (:productIds)')
            ->andWhere(\sprintf('attributes.%s != :attributeValue', $context->getAttribute()->getAttributeName()))
            ->setParameter('productIds', $result, Connection::PARAM_INT_ARRAY)
            ->setParameter('attributeValue', $context->getAttribute()->getAttributeValue())
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @return array
     */
    public function getProductOrderNumbersInCategory(Context $context)
    {
        return $this->connection->createQueryBuilder()
            ->select('detail.ordernumber')
            ->from('s_articles_details', 'detail')
            ->join('detail', 's_articles_categories_ro', 'categoryRelation', 'detail.articleID = categoryRelation.articleID')
            ->where('categoryRelation.categoryID = :categoryId')
            ->setParameter('categoryId', $context->getEventCategoryId())
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @return array
     */
    private function getProductsInCategory(Context $context)
    {
        $subQuery = $this->connection->createQueryBuilder()
            ->select('category.id')
            ->from('s_categories', 'category')
            ->where('category.path LIKE :categoryPattern')
            ->setParameter('categoryPattern', \sprintf('|%s|', $context->getSessionCategoryId()))
            ->getSQL();

        return $this->connection->createQueryBuilder()
            ->select('products.id')
            ->from('s_articles', 'products')
            ->join('products', 's_articles_categories_ro', 'productCategoryRelation', 'products.id = productCategoryRelation.articleID')
            ->where('productCategoryRelation.categoryID = :categoryId')
            ->orWhere('productCategoryRelation.categoryID IN (:subQuery)')
            ->setParameter('categoryId', $context->getSessionCategoryId())
            ->setParameter('subQuery', $subQuery)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
