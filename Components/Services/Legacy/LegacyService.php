<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Legacy;

use Doctrine\DBAL\Connection;

class LegacyService
{
    /**
     * @var Connection
     */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * @return array
     */
    public function getClassicPaymentIds()
    {
        return $this->dbConnection->createQueryBuilder()
            ->select('id')
            ->from('s_core_paymentmeans', 'pm')
            ->where("pm.name = 'paypal'")
            ->orWhere("pm.name = 'payment_paypal_installments'")
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
