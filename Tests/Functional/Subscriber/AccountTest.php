<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Subscriber\Account;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

    public function test_can_be_created()
    {
        $subscriber = $this->getSubscriber();
        static::assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents_has_correct_events()
    {
        $events = Account::getSubscribedEvents();
        static::assertCount(1, $events);
        static::assertSame('onPostDispatchAccount', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Account']);
    }

    public function test_onPostDispatchAccount_is_wrong_action()
    {
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(
            new \Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('fooBar');

        $eventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);
        $customerData = $view->getAssign('sUserData');
        static::assertSame('PayPal', $customerData['additional']['payment']['description']);
    }

    public function test_onPostDispatchAccount_no_shop()
    {
        $subscriber = $this->getSubscriber();
        $shop = Shopware()->Container()->get('shop');

        Shopware()->Container()->reset('shop');

        $view = new ViewMock(
            new \Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('payment');

        $eventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);
        $customerData = $view->getAssign('sUserData');
        static::assertSame('PayPal', $customerData['additional']['payment']['description']);
        Shopware()->Container()->set('shop', $shop);
    }

    public function test_onPostDispatchAccount_payment_method_inactive()
    {
        $paymentMethodProvider = new PaymentMethodProvider(Shopware()->Container()->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(
            new \Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $eventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);
        $customerData = $view->getAssign('sUserData');
        static::assertSame('PayPal', $customerData['additional']['payment']['description']);

        $paymentMethodProvider->setPaymentMethodActiveFlag(true);
    }

    public function test_onPostDispatchAccount_no_settings()
    {
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(
            new \Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $eventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);
        $customerData = $view->getAssign('sUserData');
        static::assertSame('PayPal', $customerData['additional']['payment']['description']);
    }

    public function test_onPostDispatchAccount_plus_not_active()
    {
        $subscriber = $this->getSubscriber();

        $this->addSettings(false);

        $view = new ViewMock(
            new \Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $eventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);
        $customerData = $view->getAssign('sUserData');
        static::assertSame('PayPal', $customerData['additional']['payment']['description']);
    }

    public function test_onPostDispatchAccount_empty_string()
    {
        $subscriber = $this->getSubscriber();

        $this->addSettings(true, '');

        $view = new ViewMock(
            new \Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $eventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);
        $customerData = $view->getAssign('sUserData');
        static::assertSame('PayPal', $customerData['additional']['payment']['description']);
    }

    public function test_onPostDispatchAccount_customer_payment()
    {
        $subscriber = $this->getSubscriber();

        $this->addSettings();

        $view = new ViewMock(
            new \Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $eventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);
        $customerData = $view->getAssign('sUserData');

        static::assertSame('PayPal, Lastschrift oder Kreditkarte', $customerData['additional']['payment']['description']);
        static::assertContains('<br>Zahlung per Lastschrift oder Kreditkarte ist auch ohne PayPal Konto möglich', $customerData['additional']['payment']['additionaldescription']);
    }

    public function test_onPostDispatchAccount_payment_methods()
    {
        $subscriber = $this->getSubscriber();

        $this->addSettings();

        $view = new ViewMock(
            new \Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $eventArgs = new \Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);
        /** @var array $paymentMethods */
        $paymentMethods = $view->getAssign('sPaymentMeans');

        $unifiedPayment = null;
        foreach ($paymentMethods as $paymentMethod) {
            if ((int) $paymentMethod['id'] === 7) {
                $unifiedPayment = $paymentMethod;
            }
        }

        static::assertNotNull($unifiedPayment);
        static::assertSame('PayPal, Lastschrift oder Kreditkarte', $unifiedPayment['description']);
        static::assertContains('<br>Zahlung per Lastschrift oder Kreditkarte ist auch ohne PayPal Konto möglich', $unifiedPayment['additionaldescription']);
    }

    /**
     * @return Account
     */
    private function getSubscriber()
    {
        $subscriber = new Account(
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('paypal_unified.dependency_provider')
        );

        return $subscriber;
    }

    /**
     * @return array
     */
    private function getAccountViewAssigns()
    {
        return require __DIR__ . '/_fixtures/account_view_assigns.php';
    }

    /**
     * @param bool   $active
     * @param string $paymentName
     */
    private function addSettings($active = true, $paymentName = 'PayPal, Lastschrift oder Kreditkarte')
    {
        $this->insertPlusSettingsFromArray([
            'active' => $active,
            'paymentName' => $paymentName,
            'paymentDescription' => 'Zahlung per Lastschrift oder Kreditkarte ist auch ohne PayPal Konto möglich',
        ]);
    }
}
