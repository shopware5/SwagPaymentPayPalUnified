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

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\ExpressCheckout;

use SwagPaymentPayPalUnified\Components\ExpressCheckout\CustomerService;

class CustomerServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_service_is_available()
    {
        $service = Shopware()->Container()->get('paypal_unified.express_checkout.customer_service');
        $this->assertEquals(CustomerService::class, get_class($service));
    }

    public function test_construct()
    {
        $service = new CustomerService(
            Shopware()->Container()->get('config'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('shopware.form.factory'),
            Shopware()->Container()->get('shopware_storefront.context_service'),
            Shopware()->Container()->get('shopware_account.register_service'),
            Shopware()->Container()->get('front'),
            Shopware()->Container()->get('paypal_unified.dependency_provider')
        );

        $this->assertNotNull($service);
    }
}
