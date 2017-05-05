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

namespace SwagPaymentPayPalUnified\Tests\Functional\Components;

use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Shop\DetachedShop;
use SwagPaymentPayPalUnified\Components\DependencyProvider;

class DependencyProviderTest extends \PHPUnit_Framework_TestCase
{
    public function test_service_available()
    {
        $this->assertEquals(DependencyProvider::class, get_class(Shopware()->Container()->get('paypal_unified.dependency_provider')));
    }

    public function test_can_be_constructed()
    {
        $dp = new DependencyProvider(Shopware()->Container());

        $this->assertNotNull($dp);
    }

    public function test_getShop_return_shop()
    {
        $dp = new DependencyProvider(Shopware()->Container());

        $this->assertEquals(DetachedShop::class, get_class($dp->getShop()));
    }

    public function test_getShop_return_null()
    {
        $dp = new DependencyProvider(new ContainerMockWithNoShop());

        $this->assertNull($dp->getShop());
    }
}

class ContainerMockWithNoShop extends Container
{
    public function has($name)
    {
        return false;
    }
}
