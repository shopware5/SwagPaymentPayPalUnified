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
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\Installments\ValidationService;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\Installments as InstallmentsSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\InstallmentsResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingRequest;
use SwagPaymentPayPalUnified\Subscriber\Installments;
use SwagPaymentPayPalUnified\Tests\Functional\UnifiedControllerTestCase;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class InstallmentsTest extends UnifiedControllerTestCase
{
    public function test_can_be_created()
    {
        $subscriber = new Installments(
            new SettingsServiceInstallmentsMock(),
            new ValidationService(),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.installments.payment_builder_service')
        );

        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $events = Installments::getSubscribedEvents();
        $this->assertCount(2, $events);
        $this->assertEquals('onPostDispatchDetail', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail']);
        $this->assertEquals([['onPostDispatchCheckout'], ['onConfirmInstallments']], $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']);
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

        $settings = new GeneralSettingsModel();
        $settings->setActive(false);
        $settingService = new SettingsServiceInstallmentsMock($settings);

        $this->assertNull($this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs));
    }

    public function test_post_dispatch_detail_installments_inactive()
    {
        $actionEventArgs = $this->getActionEventArgs();

        $settings = new GeneralSettingsModel();
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(false);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->assertNull($this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs));
    }

    public function test_post_dispatch_detail_installments_no_detail_presentment()
    {
        $actionEventArgs = $this->getActionEventArgs();

        $settings = new GeneralSettingsModel();
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeDetail(0);
        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->assertNull($this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs));
    }

    public function test_post_dispatch_detail_installments_product_price_mismatch()
    {
        $actionEventArgs = $this->getActionEventArgs();

        $settings = new GeneralSettingsModel();
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeDetail(1);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs);

        $this->assertTrue($actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsNotAvailable'));
    }

    public function test_post_dispatch_detail_installments_missing_finance_response()
    {
        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sArticle', [
            'price_numeric' => 319.99,
        ]);

        $settings = new GeneralSettingsModel();
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeDetail(1);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs);

        $this->assertNull($result);
    }

    public function test_post_dispatch_detail_installments_get_financing_response_throws_exception()
    {
        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sArticle', [
            'price_numeric' => 319.99,
        ]);

        $settings = new GeneralSettingsModel();

        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeDetail(1);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs);

        $this->assertNull($result);
    }

    public function test_post_dispatch_detail_installments_product_price_match_displayKind_simple()
    {
        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sArticle', [
            'price_numeric' => 319.99,
        ]);

        $settings = new GeneralSettingsModel();
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();

        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeDetail(1);
        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs);

        $displayKind = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsMode');

        $this->assertEquals('simple', $displayKind);
    }

    public function test_post_dispatch_detail_installments_product_price_match_displayKind_cheapest()
    {
        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sArticle', [
            'price_numeric' => 319.99,
        ]);

        $settings = new GeneralSettingsModel();
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();

        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeDetail(2);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs);

        $displayKind = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsMode');

        $this->assertEquals('cheapest', $displayKind);
    }

    public function test_OnPostDispatchCheckout_with_wrong_action()
    {
        $this->Request()->setActionName('test');

        $actionEventArgs = $this->getActionEventArgs();

        $settings = new GeneralSettingsModel();
        $settingService = new SettingsServiceInstallmentsMock($settings);
        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);

        $this->assertNull($result);
    }

    public function test_OnPostDispatchCheckout_without_active_installments_settings()
    {
        $this->Request()->setActionName('cart');

        $actionEventArgs = $this->getActionEventArgs();

        $settings = new InstallmentsSettingsModel();
        $settings->setActive(0);

        $settingService = new SettingsServiceInstallmentsMock(null, $settings);

        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);

        $this->assertNull($result);
    }

    public function test_OnPostDispatchCheckout_without_active_global_settings()
    {
        $this->Request()->setActionName('cart');

        $actionEventArgs = $this->getActionEventArgs();

        $settings = new GeneralSettingsModel();
        $settings->setActive(0);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(1);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);

        $this->assertNull($result);
    }

    public function test_OnPostDispatchCheckout_without_display_kind()
    {
        $this->Request()->setActionName('cart');

        $actionEventArgs = $this->getActionEventArgs();

        $settings = new GeneralSettingsModel();
        $settings->setActive(1);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(1);
        $instSettings->setPresentmentTypeCart(0);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);

        $this->assertNull($result);
    }

    public function test_OnPostDispatchCheckout_without_valid_price()
    {
        $this->Request()->setActionName('cart');

        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sBasket', [
            'AmountNumeric' => 9.99,
            'AmountWithTaxNumeric' => 11.11,
        ]);
        $actionEventArgs->getSubject()->View()->assign('sUserData', [
            'additional' => ['show_net' => 0],
        ]);

        $settings = new GeneralSettingsModel();
        $settings->setActive(1);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(1);
        $instSettings->setPresentmentTypeCart(1);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);

        $this->assertNull($result);
    }

    public function test_OnPostDispatchCheckout_display_kind_is_simple()
    {
        $this->Request()->setActionName('cart');

        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sBasket', [
            'AmountNumeric' => 399.99,
            'AmountWithTaxNumeric' => 444.44,
        ]);
        $actionEventArgs->getSubject()->View()->assign('sUserData', [
            'additional' => ['show_net' => 0],
        ]);

        $settings = new GeneralSettingsModel();
        $settings->setActive(1);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(1);
        $instSettings->setPresentmentTypeCart(1);
        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $displayKind = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsMode');

        $this->assertEquals('simple', $displayKind);
    }

    public function test_OnPostDispatchCheckout_display_kind_is_cheapest()
    {
        $this->Request()->setActionName('cart');

        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sBasket', [
            'AmountNumeric' => 399.99,
            'AmountWithTaxNumeric' => 444.44,
        ]);
        $actionEventArgs->getSubject()->View()->assign('sUserData', [
            'additional' => ['show_net' => 0],
        ]);

        $settings = new GeneralSettingsModel();
        $settings->setActive(1);

        $instSettings = new InstallmentsSettingsModel();

        $instSettings->setActive(1);
        $instSettings->setPresentmentTypeCart(2);
        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $displayKind = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsMode');

        $this->assertEquals('cheapest', $displayKind);
    }

    public function test_OnPostDispatchCheckout_has_correct_product_price()
    {
        $this->Request()->setActionName('cart');

        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sBasket', [
            'AmountNumeric' => 399.99,
            'AmountWithTaxNumeric' => 444.44,
        ]);
        $actionEventArgs->getSubject()->View()->assign('sUserData', [
            'additional' => ['show_net' => false],
        ]);

        $settings = new GeneralSettingsModel();
        $settings->setActive(1);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(1);
        $instSettings->setPresentmentTypeCart(2);
        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $price = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsProductPrice');

        $this->assertEquals(444.44, $price);
    }

    public function test_OnPostDispatchCheckout_has_correct_product_net_price()
    {
        $this->Request()->setActionName('cart');

        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sBasket', [
            'AmountNumeric' => 399.99,
            'AmountWithTaxNumeric' => 444.44,
        ]);
        $actionEventArgs->getSubject()->View()->assign('sUserData', [
            'additional' => ['show_net' => true],
        ]);

        $settings = new GeneralSettingsModel();
        $settings->setActive(1);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(1);
        $instSettings->setPresentmentTypeCart(2);
        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $price = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsProductPrice');

        $this->assertEquals(399.99, $price);
    }

    public function test_OnPostDispatchCheckout_has_correct_page_type()
    {
        $this->Request()->setActionName('cart');

        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sBasket', [
            'AmountNumeric' => 399.99,
            'AmountWithTaxNumeric' => 444.44,
        ]);
        $actionEventArgs->getSubject()->View()->assign('sUserData', [
            'additional' => ['show_net' => 0],
        ]);

        $settings = new GeneralSettingsModel();
        $settings->setActive(1);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(1);
        $instSettings->setPresentmentTypeCart(2);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $pageType = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsPageType');

        $this->assertEquals('cart', $pageType);
    }

    public function test_OnPostDispatchCheckout_confirm_action_with_selected_payment_method_installments()
    {
        $this->Request()->setActionName('confirm');
        $paymentMethodProvider = new PaymentMethodProvider();
        $installmentsPaymentId = $paymentMethodProvider->getPaymentId(Shopware()->Container()->get('dbal_connection'), PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);

        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sBasket', [
            'AmountNumeric' => 399.99,
            'AmountWithTaxNumeric' => 444.44,
        ]);
        $actionEventArgs->getSubject()->View()->assign('sUserData', [
            'additional' => ['show_net' => 0],
        ]);
        $actionEventArgs->getSubject()->View()->assign('sPayment', ['id' => $installmentsPaymentId]);

        $settings = new GeneralSettingsModel();
        $settings->setActive(1);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(1);
        $instSettings->setPresentmentTypeCart(1);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $requestCompleteList = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsRequestCompleteList');

        $this->assertTrue($requestCompleteList);
    }

    public function test_OnPostDispatchCheckout_confirm_action_with_selected_payment_method_not_installments()
    {
        $this->Request()->setActionName('confirm');

        $actionEventArgs = $this->getActionEventArgs();
        $actionEventArgs->getSubject()->View()->assign('sBasket', [
            'AmountNumeric' => 399.99,
            'AmountWithTaxNumeric' => 444.44,
        ]);
        $actionEventArgs->getSubject()->View()->assign('sUserData', [
            'additional' => ['show_net' => 0],
        ]);
        $actionEventArgs->getSubject()->View()->assign('sPayment', ['id' => 1]);

        $settings = new GeneralSettingsModel();
        $settings->setActive(1);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(1);
        $instSettings->setPresentmentTypeCart(1);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $requestCompleteList = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsRequestCompleteList');

        $this->assertNull($requestCompleteList);
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
            'request' => $this->Request(),
            'response' => $this->Response(),
        ]);
    }

    /**
     * @param SettingsServiceInterface $settingService
     *
     * @return Installments
     */
    private function getInstallmentsSubscriber(SettingsServiceInterface $settingService)
    {
        return new Installments(
            $settingService,
            Shopware()->Container()->get('paypal_unified.installments.validation_service'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.installments.payment_builder_service')
        );
    }
}

class SettingsServiceInstallmentsMock implements SettingsServiceInterface
{
    /**
     * @var GeneralSettingsModel
     */
    private $generalSettings;

    /**
     * @var InstallmentsSettingsModel
     */
    private $installmentsSettings;

    public function __construct(GeneralSettingsModel $generalSettings = null, InstallmentsSettingsModel $installmentsSettings = null)
    {
        $this->generalSettings = $generalSettings;
        $this->installmentsSettings = $installmentsSettings;
    }

    public function getSettings($shopId = null, $settingsTable = SettingsTable::GENERAL)
    {
        if ($settingsTable === SettingsTable::GENERAL) {
            return $this->generalSettings;
        }

        return $this->installmentsSettings;
    }

    public function get($column, $settingsTable = SettingsTable::GENERAL)
    {
    }

    public function hasSettings($settingsTable = SettingsTable::GENERAL)
    {
    }

    public function refreshDependencies()
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
