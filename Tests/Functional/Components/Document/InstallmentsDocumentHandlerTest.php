<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Document;

use Doctrine\DBAL\Connection;
use SwagPaymentPayPalUnified\Components\Document\InstallmentsDocumentHandler;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class InstallmentsDocumentHandlerTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;

    public function test_construct()
    {
        $class = new InstallmentsDocumentHandler(
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.installments.order_credit_info_service')
        );

        $this->assertNotNull($class);
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
        $this->assertEquals('TEST_PAYMENT_ID', $creditInfo['paymentId']);
        $this->assertEquals(10.01, $creditInfo['feeAmount']);
        $this->assertEquals(1400.04, $creditInfo['totalCost']);
        $this->assertEquals(67.68, $creditInfo['monthlyPayment']);
        $this->assertEquals(12, $creditInfo['term']);
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
