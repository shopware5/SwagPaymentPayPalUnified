<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;
use SwagPaymentPayPalUnified\Setup\ColumnService;

class UpdateTo430
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ColumnService
     */
    private $columnService;

    public function __construct(Connection $connection, ColumnService $columnService)
    {
        $this->connection = $connection;
        $this->columnService = $columnService;
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->addShowRatePayHintInMailColum();
        $this->insertPuiRatePayInstructionTemplateForInvoiceDocument();
    }

    /**
     * @return void
     */
    private function addShowRatePayHintInMailColum()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_pay_upon_invoice', 'show_rate_pay_hint_in_mail')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_pay_upon_invoice`
                ADD `show_rate_pay_hint_in_mail` TINYINT(1) NOT NULL default 1;'
            );
        }
    }

    /**
     * @return void
     */
    private function insertPuiRatePayInstructionTemplateForInvoiceDocument()
    {
        $template = file_get_contents(__DIR__ . '/../Assets/Document/PayPal_Unified_Ratepay_Instructions_Content.html');
        $style = \file_get_contents(__DIR__ . '/../Assets/Document/PayPal_Unified_Instructions_Content_Style.css');

        $sql = "
            INSERT INTO `s_core_documents_box` (`documentID`, `name`, `style`, `value`) VALUES
            (1, 'PayPal_Unified_Ratepay_Instructions', :style, :template);
        ";

        $this->connection->executeQuery($sql, [
            'style' => $style,
            'template' => $template,
        ]);
    }
}
