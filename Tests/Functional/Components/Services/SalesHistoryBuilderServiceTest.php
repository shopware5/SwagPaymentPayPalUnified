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

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use SwagPaymentPayPalUnified\Components\Services\SalesHistoryBuilderService;

class SalesHistoryBuilderServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_service_available()
    {
        $this->assertNotNull(Shopware()->Container()->get('paypal_unified.sales_history_builder_service'));
    }

    public function test_getSalesHistory_maxAmount()
    {
        /** @var SalesHistoryBuilderService $historyBuilderService */
        $historyBuilderService = Shopware()->Container()->get('paypal_unified.sales_history_builder_service');
        $testPaymentData = $this->getTestPaymentDetails();

        $testHistory = $historyBuilderService->getSalesHistory($testPaymentData);
        $this->assertEquals(16.939999999999998, $testHistory['maxRefundableAmount']);
    }

    public function test_getSalesHistory_count()
    {
        /** @var SalesHistoryBuilderService $historyBuilderService */
        $historyBuilderService = Shopware()->Container()->get('paypal_unified.sales_history_builder_service');
        $testPaymentData = $this->getTestPaymentDetails();

        $testHistory = $historyBuilderService->getSalesHistory($testPaymentData);
        $this->assertCount(4, $testHistory);
    }

    public function test_getSalesHistory_first_entry()
    {
        /** @var SalesHistoryBuilderService $historyBuilderService */
        $historyBuilderService = Shopware()->Container()->get('paypal_unified.sales_history_builder_service');
        $testPaymentData = $this->getTestPaymentDetails();

        $testSale = $historyBuilderService->getSalesHistory($testPaymentData)[0];
        $this->assertEquals(45.94, $testSale['amount']);
        $this->assertEquals('TEST1', $testSale['id']);
        $this->assertEquals('partially_refunded', $testSale['state']);
        $this->assertEquals('2017-01-31T09:53:36Z', $testSale['create_time']);
        $this->assertEquals('2017-01-31T13:07:06Z', $testSale['update_time']);
        $this->assertEquals('EUR', $testSale['currency']);
    }

    public function test_getSalesHistory_last_entry()
    {
        /** @var SalesHistoryBuilderService $historyBuilderService */
        $historyBuilderService = Shopware()->Container()->get('paypal_unified.sales_history_builder_service');
        $testPaymentData = $this->getTestPaymentDetails();

        $testSale = $historyBuilderService->getSalesHistory($testPaymentData)[2];
        $this->assertEquals(-24.00, $testSale['amount']);
        $this->assertEquals('TEST3', $testSale['id']);
        $this->assertEquals('completed', $testSale['state']);
        $this->assertEquals('2017-01-31T13:06:44Z', $testSale['create_time']);
        $this->assertEquals('2017-01-31T13:07:06Z', $testSale['update_time']);
        $this->assertEquals('EUR', $testSale['currency']);
    }

    /**
     * @return array
     */
    private function getTestPaymentDetails()
    {
        return require __DIR__ . '/_fixtures/PaymentFixture.php';
    }
}
