<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;

class UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSION
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
     * @return void
     */
    public function update()
    {
        $this->updateRatePayInstructionsContentTemplate();
    }

    /**
     * @return void
     */
    private function updateRatePayInstructionsContentTemplate()
    {
        $template = file_get_contents(__DIR__ . '/../Assets/Document/PayPal_Unified_Ratepay_Instructions_Content.html');

        $sql = "UPDATE s_core_documents_box SET value = :template WHERE `name` = 'PayPal_Unified_Ratepay_Instructions';";

        $this->connection->executeQuery($sql, [
            'template' => $template,
        ]);
    }
}
