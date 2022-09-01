<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Subscriber\InstallmentsBanner;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class InstallmentsBannerSubscriberTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

    const CLIENT_ID = 'testClientId';

    /**
     * @return void
     */
    public function testCanBeCreated()
    {
        $subscriber = $this->getSubscriber();
        static::assertNotNull($subscriber);
    }

    /**
     * @return void
     */
    public function testGetSubscribedEventsHasCorrectEvents()
    {
        $events = InstallmentsBanner::getSubscribedEvents();
        static::assertCount(2, $events);
        static::assertSame('onPostDispatchSecure', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend']);
        static::assertSame('onPostDispatchSecure', $events['Enlight_Controller_Action_PostDispatchSecure_Widgets']);
    }

    /**
     * @return void
     */
    public function testOnPostDispatchSecureWithoutAnySettings()
    {
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $result = $subscriber->onPostDispatchSecure($enlightEventArgs);

        static::assertNull($result);
        static::assertNull($view->getAssign('paypalUnifiedInstallmentsBanner'));
    }

    /**
     * @return void
     */
    public function testOnPostDispatchSecureReturnSettingInactive()
    {
        $subscriber = $this->getSubscriber();
        $this->createTestSettings(false);

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $subscriber->onPostDispatchSecure($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedInstallmentsBanner'));
    }

    /**
     * @return void
     */
    public function testOnPostDispatchSecurePaymentMethodInactive()
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);
        $subscriber = $this->getSubscriber();
        $this->createTestSettings();

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $subscriber->onPostDispatchSecure($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedInstallmentsBanner'));
    }

    /**
     * @return void
     */
    public function testOnPostDispatchSecureInstallmentsBannerInactive()
    {
        $paymentMethodProvider = $this->getPaymentMethodProvider();
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);
        $subscriber = $this->getSubscriber();
        $this->createTestSettings(true, false);

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $subscriber->onPostDispatchSecure($enlightEventArgs);

        static::assertNull($view->getAssign('paypalUnifiedInstallmentsBanner'));
    }

    /**
     * @return void
     */
    public function testOnPostDispatchSecureAssignsVariablesToView()
    {
        $subscriber = $this->getSubscriber();
        $this->createTestSettings();

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('foo');
        $request->setActionName('bar');
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber->onPostDispatchSecure($enlightEventArgs);

        static::assertTrue((bool) $view->getAssign('paypalUnifiedInstallmentsBanner'));
        static::assertSame(self::CLIENT_ID, $view->getAssign('paypalUnifiedInstallmentsBannerClientId'));
        static::assertSame(0.0, $view->getAssign('paypalUnifiedInstallmentsBannerAmount'));
        static::assertSame('EUR', $view->getAssign('paypalUnifiedInstallmentsBannerCurrency'));
    }

    /**
     * @return void
     */
    public function testOnPostDispatchSecureAssignsVariablesToViewProductDetailPage()
    {
        $subscriber = $this->getSubscriber();
        $this->createTestSettings();
        $productAmount = 19.99;

        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sArticle', ['price_numeric' => $productAmount]);
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('detail');
        $request->setActionName('index');
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber->onPostDispatchSecure($enlightEventArgs);

        static::assertTrue((bool) $view->getAssign('paypalUnifiedInstallmentsBanner'));
        static::assertSame(self::CLIENT_ID, $view->getAssign('paypalUnifiedInstallmentsBannerClientId'));
        static::assertSame($productAmount, $view->getAssign('paypalUnifiedInstallmentsBannerAmount'));
        static::assertSame('EUR', $view->getAssign('paypalUnifiedInstallmentsBannerCurrency'));
    }

    /**
     * @return void
     */
    public function testOnPostDispatchSecureAssignsVariablesToViewCartPage()
    {
        $subscriber = $this->getSubscriber();
        $this->createTestSettings();
        $cartAmount = 19.99;

        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sBasket', ['AmountNumeric' => $cartAmount]);
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('checkout');
        $request->setActionName('cart');
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber->onPostDispatchSecure($enlightEventArgs);

        static::assertTrue((bool) $view->getAssign('paypalUnifiedInstallmentsBanner'));
        static::assertSame(self::CLIENT_ID, $view->getAssign('paypalUnifiedInstallmentsBannerClientId'));
        static::assertSame($cartAmount, $view->getAssign('paypalUnifiedInstallmentsBannerAmount'));
        static::assertSame('EUR', $view->getAssign('paypalUnifiedInstallmentsBannerCurrency'));
    }

    /**
     * @return void
     */
    public function testOnPostDispatchSecureAssignsBuyerCountryDEToView()
    {
        $subscriber = $this->getSubscriber();
        $this->createTestSettings();

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('detail');
        $request->setActionName('index');
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber->onPostDispatchSecure($enlightEventArgs);

        static::assertSame('DE', $view->getAssign('paypalUnifiedInstallmentsBannerBuyerCountry'));
    }

    /**
     * @return void
     */
    public function testOnPostDispatchSecureAssignsBuyerCountryGBToView()
    {
        $subscriber = $this->getSubscriber();
        $this->createTestSettings();

        $sql = \file_get_contents(__DIR__ . '/_fixtures/install_great_britan_pounds.sql');
        static::assertTrue(\is_string($sql));
        $currencyId = 123;
        $this->getContainer()->get('dbal_connection')->executeUpdate($sql, ['currencyId' => $currencyId]);

        $contextService = $this->getContainer()->get('shopware_storefront.context_service');
        $tmpShopContext = $contextService->getShopContext();
        $shopContext = $contextService->createShopContext(2, $currencyId, 'EK');

        $reflectionProperty = (new \ReflectionClass(ContextService::class))->getProperty('context');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($contextService, $shopContext);

        $view = new ViewMock(new Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('detail');
        $request->setActionName('index');
        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber->onPostDispatchSecure($enlightEventArgs);

        $reflectionProperty->setValue($contextService, $tmpShopContext);

        static::assertSame('GB', $view->getAssign('paypalUnifiedInstallmentsBannerBuyerCountry'));
    }

    /**
     * @return InstallmentsBanner
     */
    private function getSubscriber()
    {
        return new InstallmentsBanner(
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('paypal_unified.payment_method_provider')
        );
    }

    /**
     * @param bool $active
     * @param bool $advertiseInstallments
     *
     * @return void
     */
    private function createTestSettings($active = true, $advertiseInstallments = true)
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'clientId' => self::CLIENT_ID,
            'active' => $active,
        ]);

        $this->insertInstallmentsSettingsFromArray([
            'shopId' => 1,
            'advertiseInstallments' => $advertiseInstallments,
        ]);
    }

    /**
     * @return PaymentMethodProvider
     */
    private function getPaymentMethodProvider()
    {
        return new PaymentMethodProvider(
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('models')
        );
    }
}
