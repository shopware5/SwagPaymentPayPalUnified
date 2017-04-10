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

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Template_Manager;
use SwagPaymentPayPalUnified\Subscriber\Backend;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class BackendSubscriberTest extends \Enlight_Components_Test_Controller_TestCase
{
    public function test_can_be_created()
    {
        $subscriber = new Backend(__DIR__);
        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents_has_correct_events()
    {
        $events = Backend::getSubscribedEvents();
        $this->assertEquals('onLoadBackendIndex', $events['Enlight_Controller_Action_PostDispatchSecure_Backend_Index']);
    }

    public function test_onLoadBackendIndex_extends_template()
    {
        $subscriber = new Backend(Shopware()->Container()->getParameter('paypal_unified.plugin_dir'));

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $this->Request()->setActionName('index');

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($this->Request(), $view),
        ]);

        $subscriber->onLoadBackendIndex($enlightEventArgs);

        $this->assertCount(1, $view->getTemplateDir());
    }
}
