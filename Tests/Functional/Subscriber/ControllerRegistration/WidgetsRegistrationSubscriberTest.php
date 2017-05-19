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

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\ControllerRegistration;

use SwagPaymentPayPalUnified\Subscriber\ControllerRegistration\Widgets;

class WidgetsRegistrationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function test_can_be_created()
    {
        $subscriber = new Widgets(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));
        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $events = Widgets::getSubscribedEvents();
        $this->assertCount(2, $events);
        $this->assertEquals('onGetInstallmentsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Widgets_PaypalUnifiedInstallments']);
        $this->assertEquals('onGetInstallmentsControllerPath', $events['Enlight_Controller_Dispatcher_ControllerPath_Widgets_PaypalUnifiedInstallments']);
    }

    public function test_onGetInstallmentsControllerPath()
    {
        $subscriber = new Widgets(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));
        $path = $subscriber->onGetInstallmentsControllerPath();

        $this->assertFileExists($path);
    }

    public function test_onGetEcControllerPath()
    {
        $subscriber = new Widgets(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));
        $path = $subscriber->onGetEcControllerPath();

        $this->assertFileExists($path);
    }
}
