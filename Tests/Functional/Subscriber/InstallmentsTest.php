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

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\Services\Installments\ValidationService;
use SwagPaymentPayPalUnified\Models\Settings;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\InstallmentsResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingRequest;
use SwagPaymentPayPalUnified\Subscriber\Installments;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class InstallmentsTest extends \PHPUnit_Framework_TestCase
{
    public function test_can_be_created()
    {
        $pluginLogger = Shopware()->Container()->get('pluginlogger');
        $subscriber = new Installments(
            new SettingsServiceInstallmentsMock(),
            new ValidationService(),
            new InstallmentsResourceMock(),
            $pluginLogger
        );

        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $events = Installments::getSubscribedEvents();
        $this->assertCount(1, $events);
        $this->assertEquals('onPostDispatchDetail', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail']);
    }

    public function test_post_dispatch_detail_no_settings()
    {
        $actionEventArgs = $this->getActionEventArgs();

        $settingService = new SettingsServiceInstallmentsMock(null);

        $this->assertNull($this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs));
    }

    public function test_post_dispatch_detail_unified_inactive()
    {
        $actionEventArgs = $this->getActionEventArgs();

        $settings = new Settings();
        $settings->setActive(false);
        $settingService = new SettingsServiceInstallmentsMock($settings);

        $this->assertNull($this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs));
    }

    public function test_post_dispatch_detail_installments_inactive()
    {
        $actionEventArgs = $this->getActionEventArgs();

        $settings = new Settings();
        $settings->setActive(true);
        $settings->setInstallmentsActive(false);
        $settingService = new SettingsServiceInstallmentsMock($settings);

        $this->assertNull($this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs));
    }

    public function test_post_dispatch_detail_installments_no_detail_presentment()
    {
        $actionEventArgs = $this->getActionEventArgs();

        $settings = new Settings();
        $settings->setActive(true);
        $settings->setInstallmentsActive(true);
        $settings->setInstallmentsPresentmentDetail(0);
        $settingService = new SettingsServiceInstallmentsMock($settings);

        $this->assertNull($this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs));
    }

    public function test_post_dispatch_detail_installments_product_price_dismatch()
    {
        $actionEventArgs = $this->getActionEventArgs();

        $settings = new Settings();
        $settings->setActive(true);
        $settings->setInstallmentsActive(true);
        $settings->setInstallmentsPresentmentDetail(1);
        $settingService = new SettingsServiceInstallmentsMock($settings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs);

        $this->assertTrue($actionEventArgs->getSubject()->View()->getAssign('payPalUnifiedInstallmentsNotAvailable'));
    }

    public function test_post_dispatch_detail_installments_missing_finance_response()
    {
        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sArticle', [
            'price_numeric' => 319.99,
        ]);

        $settings = new Settings();
        $settings->setActive(true);
        $settings->setInstallmentsActive(true);
        $settings->setInstallmentsPresentmentDetail(1);
        $settingService = new SettingsServiceInstallmentsMock($settings);

        $result = $this->getInstallmentsSubscriber($settingService, 2)->onPostDispatchDetail($actionEventArgs);

        $this->assertNull($result);
    }

    public function test_post_dispatch_detail_installments_get_financing_response_throws_exception()
    {
        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sArticle', [
            'price_numeric' => 319.99,
        ]);

        $settings = new Settings();
        $settings->setActive(true);
        $settings->setInstallmentsActive(true);
        $settings->setInstallmentsPresentmentDetail(1);
        $settingService = new SettingsServiceInstallmentsMock($settings);

        $result = $this->getInstallmentsSubscriber($settingService, 3)->onPostDispatchDetail($actionEventArgs);

        $this->assertNull($result);
    }

    public function test_post_dispatch_detail_installments_product_price_match_displayKind_simple()
    {
        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sArticle', [
            'price_numeric' => 319.99,
        ]);

        $settings = new Settings();
        $settings->setActive(true);
        $settings->setInstallmentsActive(true);
        $settings->setInstallmentsPresentmentDetail(1);
        $settingService = new SettingsServiceInstallmentsMock($settings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs);

        $displayKind = $actionEventArgs->getSubject()->View()->getAssign('payPalUnifiedInstallmentsDisplayKind');

        $this->assertEquals('simple', $displayKind);
    }

    public function test_post_dispatch_detail_installments_product_price_match_displayKind_cheapest()
    {
        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sArticle', [
            'price_numeric' => 319.99,
        ]);

        $settings = new Settings();
        $settings->setActive(true);
        $settings->setInstallmentsActive(true);
        $settings->setInstallmentsPresentmentDetail(2);
        $settingService = new SettingsServiceInstallmentsMock($settings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs);

        $displayKind = $actionEventArgs->getSubject()->View()->getAssign('payPalUnifiedInstallmentsDisplayKind');

        $this->assertEquals('cheapest', $displayKind);
    }

    /**
     * @return \Enlight_Controller_ActionEventArgs
     */
    private function getActionEventArgs()
    {
        $controllerMock = new DummyController(
            new \Enlight_Controller_Request_RequestTestCase(),
            new ViewMock(new \Enlight_Template_Manager())
        );

        return new \Enlight_Controller_ActionEventArgs([
            'subject' => $controllerMock,
        ]);
    }

    /**
     * @param     $settingService
     * @param int $financingMode
     *
     * @return Installments
     */
    private function getInstallmentsSubscriber($settingService, $financingMode = 1)
    {
        $validationService = Shopware()->Container()->get('paypal_unified.installments.validation_service');

        if ($financingMode === 1) {
            $installmentsResource = new InstallmentsResourceMock(require __DIR__ . '/_fixtures/installments_response.php');
        } elseif ($financingMode === 2) {
            $installmentsResource = new InstallmentsResourceMock(null, 2);
        } else {
            $installmentsResource = new InstallmentsResourceMock(null, 3);
        }

        return new Installments($settingService, $validationService, $installmentsResource);
    }
}

class SettingsServiceInstallmentsMock implements SettingsServiceInterface
{
    private $settings;

    public function __construct(Settings $settings = null)
    {
        $this->settings = $settings;
    }

    public function getSettings($shopId = null)
    {
        return $this->settings;
    }

    public function get($column)
    {
    }

    public function hasSettings()
    {
    }
}

class InstallmentsResourceMock extends InstallmentsResource
{
    private $financing;

    private $mode;

    /**
     * @param array|null $financing
     * @param int        $mode
     */
    public function __construct(array $financing = null, $mode = 1)
    {
        $this->financing = $financing;

        $clientService = Shopware()->Container()->get('paypal_unified.client_service');
        parent::__construct($clientService);
        $this->mode = $mode;
    }

    /**
     * {@inheritdoc}
     */
    public function getFinancingOptions(FinancingRequest $financingRequest)
    {
        if ($this->mode === 3) {
            throw new RequestException('Installments test throws exception hooray');
        }

        return $this->financing;
    }
}
