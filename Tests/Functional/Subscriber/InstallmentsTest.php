<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\Installments\ValidationService;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\Installments as InstallmentsSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\Subscriber\Installments;
use SwagPaymentPayPalUnified\Tests\Functional\UnifiedControllerTestCase;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\OrderCreditInfoServiceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\PaymentResourceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class InstallmentsTest extends UnifiedControllerTestCase
{
    const INSTALLMENTS_PAYMENT_ID = 'installmentsPaymentId';

    public function test_can_be_created()
    {
        $subscriber = new Installments(
            new SettingsServiceInstallmentsMock(),
            new ValidationService(),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.installments.payment_builder_service'),
            Shopware()->Container()->get('paypal_unified.exception_handler_service'),
            new PaymentResourceMock(),
            new OrderCreditInfoServiceMock(),
            Shopware()->Container()->get('paypal_unified.client_service')
        );

        static::assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $events = Installments::getSubscribedEvents();
        static::assertCount(2, $events);
        static::assertSame('onPostDispatchDetail', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail']);
        static::assertSame([['onPostDispatchCheckout'], ['onConfirmInstallments']], $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout']);
    }

    public function test_post_dispatch_detail_no_settings()
    {
        $actionEventArgs = $this->getActionEventArgs();

        $settingService = new SettingsServiceInstallmentsMock(null);

        static::assertNull($this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs));
    }

    public function test_post_dispatch_detail_payment_method_inactive()
    {
        $paymentMethodProvider = new PaymentMethodProvider(Shopware()->Container()->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(false, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);

        $actionEventArgs = $this->getActionEventArgs();

        $settings = new GeneralSettingsModel();
        $settings->setActive(true);
        $settingService = new SettingsServiceInstallmentsMock($settings);

        static::assertNull($this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs));

        $paymentMethodProvider->setPaymentMethodActiveFlag(true, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);
    }

    public function test_post_dispatch_detail_unified_inactive()
    {
        $actionEventArgs = $this->getActionEventArgs();

        $settings = new GeneralSettingsModel();
        $settings->setActive(false);
        $settingService = new SettingsServiceInstallmentsMock($settings);

        static::assertNull($this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs));
    }

    public function test_post_dispatch_detail_installments_inactive()
    {
        $actionEventArgs = $this->getActionEventArgs();

        $settings = new GeneralSettingsModel();
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(false);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        static::assertNull($this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs));
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

        static::assertNull($this->getInstallmentsSubscriber($settingService)->onPostDispatchDetail($actionEventArgs));
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

        static::assertTrue($actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsNotAvailable'));
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

        static::assertNull($result);
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

        static::assertNull($result);
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

        static::assertSame('simple', $displayKind);
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

        static::assertSame('cheapest', $displayKind);
    }

    public function test_OnPostDispatchCheckout_with_wrong_action()
    {
        $this->Request()->setActionName('test');

        $actionEventArgs = $this->getActionEventArgs();

        $settings = new GeneralSettingsModel();
        $settingService = new SettingsServiceInstallmentsMock($settings);
        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);

        static::assertNull($result);
    }

    public function test_OnPostDispatchCheckout_express_checkout()
    {
        $this->Request()->setParam('expressCheckout', 1);

        $actionEventArgs = $this->getActionEventArgs();

        $settings = new GeneralSettingsModel();
        $settingService = new SettingsServiceInstallmentsMock($settings);
        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);

        static::assertNull($result);
    }

    public function test_OnPostDispatchCheckout_payment_method_inactive()
    {
        $paymentMethodProvider = new PaymentMethodProvider(Shopware()->Container()->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(false, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);

        $this->Request()->setActionName('cart');

        $actionEventArgs = $this->getActionEventArgs();

        $settings = new InstallmentsSettingsModel();
        $settings->setActive(true);

        $settingService = new SettingsServiceInstallmentsMock(null, $settings);

        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);

        static::assertNull($result);

        $paymentMethodProvider->setPaymentMethodActiveFlag(true, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);
    }

    public function test_OnPostDispatchCheckout_without_active_installments_settings()
    {
        $this->Request()->setActionName('cart');

        $actionEventArgs = $this->getActionEventArgs();

        $generalSettings = new GeneralSettingsModel();
        $generalSettings->setActive(true);

        $settings = new InstallmentsSettingsModel();
        $settings->setActive(false);

        $settingService = new SettingsServiceInstallmentsMock($generalSettings, $settings);

        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);

        static::assertNull($result);
    }

    public function test_OnPostDispatchCheckout_without_active_global_settings()
    {
        $this->Request()->setActionName('cart');

        $actionEventArgs = $this->getActionEventArgs();

        $settings = new GeneralSettingsModel();
        $settings->setActive(false);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(true);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);

        static::assertNull($result);
    }

    public function test_OnPostDispatchCheckout_without_display_kind()
    {
        $this->Request()->setActionName('cart');

        $actionEventArgs = $this->getActionEventArgs();

        $settings = new GeneralSettingsModel();
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeCart(0);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);

        static::assertNull($result);
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
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeCart(1);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $result = $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);

        static::assertNull($result);
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
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeCart(1);
        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $displayKind = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsMode');

        static::assertSame('simple', $displayKind);
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
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();

        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeCart(2);
        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $displayKind = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsMode');

        static::assertSame('cheapest', $displayKind);
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
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeCart(2);
        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $price = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsProductPrice');

        static::assertSame('444.44', $price);
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
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeCart(2);
        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $price = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsProductPrice');

        static::assertSame('399.99', $price);
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
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeCart(2);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $pageType = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsPageType');

        static::assertSame('cart', $pageType);
    }

    public function test_OnPostDispatchCheckout_confirm_action_with_selected_payment_method_installments()
    {
        $this->Request()->setActionName('confirm');
        $installmentsPaymentId = (new PaymentMethodProvider())->getPaymentId(
            Shopware()->Container()->get('dbal_connection'),
            PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME
        );

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
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeCart(1);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $requestCompleteList = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsRequestCompleteList');

        static::assertTrue($requestCompleteList);
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
        $settings->setActive(true);

        $instSettings = new InstallmentsSettingsModel();
        $instSettings->setActive(true);
        $instSettings->setPresentmentTypeCart(1);

        $settingService = new SettingsServiceInstallmentsMock($settings, $instSettings);

        $this->getInstallmentsSubscriber($settingService)->onPostDispatchCheckout($actionEventArgs);
        $requestCompleteList = $actionEventArgs->getSubject()->View()->getAssign('paypalInstallmentsRequestCompleteList');

        static::assertNull($requestCompleteList);
    }

    public function test_onConfirmInstallments_wrong_action()
    {
        $this->Request()->setActionName('foo');
        $actionEventArgs = $this->getActionEventArgs();
        $settingService = new SettingsServiceInstallmentsMock();

        $subscriber = $this->getInstallmentsSubscriber($settingService);
        $subscriber->onConfirmInstallments($actionEventArgs);

        $assign = $actionEventArgs->getSubject()->View()->getAssign();

        static::assertEmpty($assign);
    }

    public function test_onConfirmInstallments_without_flag()
    {
        $this->Request()->setActionName('confirm');
        $actionEventArgs = $this->getActionEventArgs();
        $settingService = new SettingsServiceInstallmentsMock();

        $subscriber = $this->getInstallmentsSubscriber($settingService);
        $subscriber->onConfirmInstallments($actionEventArgs);

        $assign = $actionEventArgs->getSubject()->View()->getAssign();

        static::assertEmpty($assign);
    }

    public function test_onConfirmInstallments_without_paymentId_and_payer()
    {
        $this->Request()->setActionName('confirm');
        $this->Request()->setParam('installments', true);
        $actionEventArgs = $this->getActionEventArgs();
        $settingService = new SettingsServiceInstallmentsMock();

        $subscriber = $this->getInstallmentsSubscriber($settingService);
        $subscriber->onConfirmInstallments($actionEventArgs);

        $assign = $actionEventArgs->getSubject()->View()->getAssign();

        static::assertEmpty($assign);
    }

    public function test_onConfirmInstallments_throws_exception()
    {
        $this->Request()->setActionName('confirm');
        $this->Request()->setParam('installments', true);
        $this->Request()->setParam('paymentId', 'exception');
        $this->Request()->setParam('PayerID', 'payerId');
        $this->Request()->setParam('basketId', false);

        $actionEventArgs = $this->getActionEventArgs();

        $settingService = new SettingsServiceInstallmentsMock();

        $this->getInstallmentsSubscriber($settingService)->onConfirmInstallments($actionEventArgs);

        static::assertContains(
            'checkout/shippingPayment/paypal_unified_error_code/5/paypal_unified_error_name/0/paypal_unified_error_message',
            $this->Response()->getHeader('Location')
        );
        static::assertTrue($this->Response()->isRedirect());
    }

    public function test_onConfirmInstallments_success()
    {
        $this->Request()->setActionName('confirm');
        $this->Request()->setParam('installments', true);
        $this->Request()->setParam('paymentId', self::INSTALLMENTS_PAYMENT_ID);
        $this->Request()->setParam('PayerID', 'payerId');
        $actionEventArgs = $this->getActionEventArgs();
        $settingService = new SettingsServiceInstallmentsMock();

        $this->getInstallmentsSubscriber($settingService)->onConfirmInstallments($actionEventArgs);
        $viewAssignments = $actionEventArgs->getSubject()->View()->getAssign();

        static::assertSame(self::INSTALLMENTS_PAYMENT_ID, $viewAssignments['paypalInstallmentsPaymentId']);
        static::assertSame('payerId', $viewAssignments['paypalInstallmentsPayerId']);
    }

    /**
     * @return \Enlight_Controller_ActionEventArgs
     */
    private function getActionEventArgs()
    {
        $controllerMock = new DummyController(
            $this->Request(),
            new ViewMock(new \Enlight_Template_Manager()),
            $this->Response()
        );

        return new \Enlight_Controller_ActionEventArgs([
            'subject' => $controllerMock,
            'request' => $this->Request(),
            'response' => $this->Response(),
        ]);
    }

    /**
     * @return Installments
     */
    private function getInstallmentsSubscriber(SettingsServiceInterface $settingService)
    {
        return new Installments(
            $settingService,
            Shopware()->Container()->get('paypal_unified.installments.validation_service'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.installments.payment_builder_service'),
            Shopware()->Container()->get('paypal_unified.exception_handler_service'),
            new PaymentResourceMock(),
            new OrderCreditInfoServiceMock(),
            Shopware()->Container()->get('paypal_unified.client_service')
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
