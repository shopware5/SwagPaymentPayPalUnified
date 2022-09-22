<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

use Doctrine\DBAL\Connection;
use Exception;
use PDO;
use Shopware\Components\NumberRangeIncrementerInterface;

class NumberRangeIncrementerDecorator implements NumberRangeIncrementerInterface
{
    const NAME_INVOICE = 'invoice';

    const POOL_DATABASE_TABLE_NAME = 'swag_payment_paypal_unified_order_number_pool';

    const ORDERNUMBER_SESSION_KEY = 'swagPayPalUnifiedReservedOrderNumber';

    /**
     * @var NumberRangeIncrementerInterface
     */
    private $numberRangeIncrementer;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    public function __construct(NumberRangeIncrementerInterface $numberRangeIncrementer, Connection $connection, DependencyProvider $dependencyProvider)
    {
        $this->numberRangeIncrementer = $numberRangeIncrementer;
        $this->connection = $connection;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($name)
    {
        if ($name !== self::NAME_INVOICE) {
            return $this->numberRangeIncrementer->increment($name);
        }

        if ($this->dependencyProvider->getSession()->offsetExists(self::ORDERNUMBER_SESSION_KEY)) {
            return $this->dependencyProvider->getSession()->offsetGet(self::ORDERNUMBER_SESSION_KEY);
        }

        try {
            $this->connection->beginTransaction();

            $orderNumberFromPool = $this->getOrderNumberFromPool();
            if ($orderNumberFromPool !== null) {
                $this->deleteOrderNumberFromPool((int) $orderNumberFromPool['id']);

                $this->dependencyProvider->getSession()->offsetSet(self::ORDERNUMBER_SESSION_KEY, $orderNumberFromPool['order_number']);

                $this->connection->commit();

                return $orderNumberFromPool['order_number'];
            }

            $this->connection->commit();
        } catch (Exception $exception) {
            $this->connection->rollBack();

            throw $exception;
        }

        $ordernumber = $this->numberRangeIncrementer->increment($name);

        $this->dependencyProvider->getSession()->offsetSet(self::ORDERNUMBER_SESSION_KEY, $ordernumber);

        return $ordernumber;
    }

    /**
     * @return array<string,mixed>|null
     */
    private function getOrderNumberFromPool()
    {
        $orderNumberFromPool = $this->connection->createQueryBuilder()
            ->select(['id', 'order_number'])
            ->from(self::POOL_DATABASE_TABLE_NAME)
            ->setMaxResults(1)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);

        if (!\is_array($orderNumberFromPool)) {
            return null;
        }

        return $orderNumberFromPool;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    private function deleteOrderNumberFromPool($id)
    {
        $this->connection->createQueryBuilder()
            ->delete(self::POOL_DATABASE_TABLE_NAME)
            ->where('id = :id')
            ->setParameter('id', $id)
            ->execute();
    }
}
