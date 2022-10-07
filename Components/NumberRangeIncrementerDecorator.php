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
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;

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

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function __construct(
        NumberRangeIncrementerInterface $numberRangeIncrementer,
        Connection $connection,
        DependencyProvider $dependencyProvider,
        LoggerServiceInterface $logger
    ) {
        $this->numberRangeIncrementer = $numberRangeIncrementer;
        $this->connection = $connection;
        $this->dependencyProvider = $dependencyProvider;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($name)
    {
        if ($name !== self::NAME_INVOICE) {
            return $this->numberRangeIncrementer->increment($name);
        }

        $this->logger->debug(sprintf('%s START', __METHOD__));

        if ($this->dependencyProvider->getSession()->offsetExists(self::ORDERNUMBER_SESSION_KEY)) {
            $ordernumberFromSession = $this->dependencyProvider->getSession()->offsetGet(self::ORDERNUMBER_SESSION_KEY);

            $this->logger->debug(sprintf('%s RETURN ORDERNUMBER FROM SESSION: %s', __METHOD__, $ordernumberFromSession));

            return $ordernumberFromSession;
        }

        try {
            $this->connection->beginTransaction();

            $orderNumberFromPool = $this->getOrderNumberFromPool();
            if (\is_array($orderNumberFromPool)) {
                $this->deleteOrderNumberFromPool((int) $orderNumberFromPool['id']);

                $this->dependencyProvider->getSession()->offsetSet(self::ORDERNUMBER_SESSION_KEY, $orderNumberFromPool['order_number']);

                $this->connection->commit();

                $this->logger->debug(sprintf('%s RETURN ORDERNUMBER FROM POOL: %s', __METHOD__, $orderNumberFromPool['order_number']));

                return $orderNumberFromPool['order_number'];
            }

            $this->connection->commit();
        } catch (Exception $exception) {
            $this->connection->rollBack();

            throw $exception;
        }

        $ordernumber = $this->numberRangeIncrementer->increment($name);

        $this->dependencyProvider->getSession()->offsetSet(self::ORDERNUMBER_SESSION_KEY, $ordernumber);

        $this->logger->debug(sprintf('%s RETURN ORDERNUMBER FROM ORIGINAL SERVICE: %s', __METHOD__, $ordernumber));

        return $ordernumber;
    }

    /**
     * @return array<string,mixed>|null
     */
    private function getOrderNumberFromPool()
    {
        $this->logger->debug(sprintf('%s GET ORDERNUMBER FROM POOL', __METHOD__));

        $orderNumberFromPool = $this->connection->createQueryBuilder()
            ->select(['id', 'order_number'])
            ->from(self::POOL_DATABASE_TABLE_NAME)
            ->setMaxResults(1)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);

        if (!\is_array($orderNumberFromPool)) {
            $this->logger->debug(sprintf('%s NO ORDERNUMBER IN POOL FOUND', __METHOD__));

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
        $this->logger->debug(sprintf('%s DELETE ORDERNUMBER FROM POOL WITH ID: %d', __METHOD__, $id));

        $this->connection->createQueryBuilder()
            ->delete(self::POOL_DATABASE_TABLE_NAME)
            ->where('id = :id')
            ->setParameter('id', $id)
            ->execute();
    }
}
