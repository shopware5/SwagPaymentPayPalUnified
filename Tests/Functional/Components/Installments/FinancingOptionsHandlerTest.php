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

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Installments;

use SwagPaymentPayPalUnified\Components\Installments\FinancingOptionsHandler;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse;

class FinancingOptionsHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function test_can_be_constructed()
    {
        $service = new FinancingOptionsHandler(new FinancingResponse());

        $this->assertNotNull($service);
    }

    public function test_sortOptionsBy_by_term()
    {
        $service = new FinancingOptionsHandler(FinancingResponse::fromArray($this->getFinancingFixture()['financing_options'][0]));

        $sorted = $service->sortOptionsBy(FinancingOptionsHandler::SORT_BY_TERM)->toArray()['qualifyingFinancingOptions'];

        $this->assertEquals(6, $sorted[0]['creditFinancing']['term']);
        $this->assertEquals(12, $sorted[1]['creditFinancing']['term']);
        $this->assertEquals(18, $sorted[2]['creditFinancing']['term']);
        $this->assertEquals(24, $sorted[3]['creditFinancing']['term']);
    }

    public function test_sortOptionsBy_by_monthly_payment()
    {
        $service = new FinancingOptionsHandler(FinancingResponse::fromArray($this->getFinancingFixture()['financing_options'][0]));

        $sorted = $service->sortOptionsBy(FinancingOptionsHandler::SORT_BY_MONTHLY_PAYMENT)->toArray()['qualifyingFinancingOptions'];

        $this->assertEquals(29.49, $sorted[0]['monthlyPayment']['value']);
        $this->assertEquals(38.42, $sorted[1]['monthlyPayment']['value']);
        $this->assertEquals(56.3, $sorted[2]['monthlyPayment']['value']);
        $this->assertEquals(106.98, $sorted[3]['monthlyPayment']['value']);
    }

    private function getFinancingFixture()
    {
        return require __DIR__ . '/_fixtures/FinancingResponseFixture.php';
    }
}
