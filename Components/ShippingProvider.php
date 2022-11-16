<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

use Doctrine\DBAL\Connection;

class ShippingProvider
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * @param int $shippingId
     *
     * @return string|null
     */
    public function getCarrierByShippingId($shippingId)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('swag_paypal_unified_carrier')
            ->from('s_premium_dispatch_attributes', 'shipping_attributes')
            ->where('dispatchId = :id')
            ->andWhere('shipping_attributes.swag_paypal_unified_carrier IS NOT NULL')
            ->andWhere('shipping_attributes.swag_paypal_unified_carrier <> \'\'')
            ->setMaxResults(1)
            ->setParameter('id', $shippingId);

        $result = $queryBuilder->execute()->fetchColumn();

        if (!\is_string($result)) {
            return null;
        }

        return $result;
    }
}
