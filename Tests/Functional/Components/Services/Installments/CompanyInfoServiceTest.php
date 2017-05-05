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

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Installments;

use SwagPaymentPayPalUnified\Components\Services\Installments\CompanyInfoService;

class CompanyInfoServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_can_be_constructed()
    {
        $service = new CompanyInfoService(Shopware()->Container()->get('config'));

        $this->assertNotNull($service);
    }

    public function test_is_available()
    {
        $service = Shopware()->Container()->get('paypal_unified.installments.company_info_service');

        $this->assertEquals(CompanyInfoService::class, get_class($service));
    }

    public function test_getCompanyInfo()
    {
        $service = new CompanyInfoService(new ShopwareAddressConfigMock([]));
        $result = $service->getCompanyInfo();

        $this->assertCount(2, $result);
        $this->assertEquals('Test address', $result['address']);
        $this->assertEquals('Test company', $result['name']);
    }
}

class ShopwareAddressConfigMock extends \Shopware_Components_Config
{
    public function __construct(array $config)
    {
    }

    public function get($name, $default = null)
    {
        if ($name === 'address') {
            return 'Test address';
        } elseif ($name === 'company') {
            return 'Test company';
        }

        return null;
    }
}
