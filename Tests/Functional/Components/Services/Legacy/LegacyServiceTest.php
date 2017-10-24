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

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Legacy;

use SwagPaymentPayPalUnified\Components\Services\Legacy\LegacyService;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class LegacyServiceTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;

    public function test_construct()
    {
        $service = new LegacyService(Shopware()->Container()->get('dbal_connection'));

        $this->assertInstanceOf(LegacyService::class, $service);
    }

    public function test_getClassicPaymentId_returns_correct_id()
    {
        $this->insertClassicPayment();

        $service = Shopware()->Container()->get('paypal_unified.legacy_service');

        $this->assertNotFalse($service->getClassicPaymentId());
    }

    private function insertClassicPayment()
    {
        $sql = "INSERT INTO s_core_paymentmeans(name, description, template, class, `table`, hide, additionaldescription, surchargestring, `position`, esdactive, embediframe, hideprospect) 
				VALUES ('paypal', 'PayPal Classic', '', '', 0, 'PayPal Classic legacy', '', 0, 1, 0, 0, 0);";

        Shopware()->Db()->executeUpdate($sql);
    }
}
