<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Document;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Document\InstallmentsDocumentHandler;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class InstallmentsDocumentHandlerTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function test_construct()
    {
        $class = new InstallmentsDocumentHandler(
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.installments.order_credit_info_service')
        );

        static::assertNotNull($class);
    }

    public function test_handleDocument()
    {
        $orderNumber = 20001;
        $document = new DocumentMock();

        $this->insertTestData();

        $handler = new InstallmentsDocumentHandler(
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.installments.order_credit_info_service')
        );

        $handler->handleDocument($orderNumber, $document);

        $creditInfo = $document->_view->getVariable('paypalInstallmentsCredit')->value;
        static::assertEquals('TEST_PAYMENT_ID', $creditInfo['paymentId']);
        static::assertEquals(10.01, $creditInfo['feeAmount']);
        static::assertEquals(1400.04, $creditInfo['totalCost']);
        static::assertEquals(67.68, $creditInfo['monthlyPayment']);
        static::assertEquals(12, $creditInfo['term']);
    }

    private function insertTestData()
    {
        /** @var Connection $db */
        $db = Shopware()->Container()->get('dbal_connection');

        $sql = "INSERT INTO `swag_payment_paypal_unified_financing_information` (`payment_id`, `fee_amount`, `total_cost`, `term`, `monthly_payment`)
                VALUES ('TEST_PAYMENT_ID', 10.01, 1400.04, 12, 67.68);";
        $db->executeUpdate($sql);

        $sql = "UPDATE s_order SET temporaryID='TEST_PAYMENT_ID' WHERE id=15";
        $db->executeUpdate($sql);
    }
}

class DocumentMock extends \Shopware_Components_Document
{
    /**
     * @var \Smarty_Data
     */
    public $_view;

    public function __construct()
    {
        $this->_view = new \Smarty_Data();
    }
}
