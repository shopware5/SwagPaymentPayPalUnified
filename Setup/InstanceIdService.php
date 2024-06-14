<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup;

use Doctrine\DBAL\Connection;
use RuntimeException;
use SwagPaymentPayPalUnified\Components\Uuid;

final class InstanceIdService
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
     * @return string
     */
    public function getInstanceId()
    {
        $instanceId = $this->get();

        if ($instanceId === null) {
            $instanceId = $this->create();
        }

        return $instanceId;
    }

    /**
     * @return string|null
     */
    private function get()
    {
        $result = $this->connection->createQueryBuilder()
            ->select('instance_id')
            ->from('swag_payment_paypal_unified_instance')
            ->execute()
            ->fetchColumn();

        if (!$result) {
            return null;
        }

        return $result;
    }

    /**
     * @return string
     */
    private function create()
    {
        $instanceId = Uuid::generateUuid();

        $this->connection->createQueryBuilder()
            ->insert('swag_payment_paypal_unified_instance')
            ->values(['instance_id' => ':instanceId'])
            ->setParameter('instanceId', $instanceId)
            ->execute();

        if ($instanceId !== $this->get()) {
            throw new RuntimeException('Could not create instance id');
        }

        return $instanceId;
    }
}
