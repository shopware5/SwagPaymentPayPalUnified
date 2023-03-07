<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Controller_ActionEventArgs;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Subscriber\Account;
use SwagPaymentPayPalUnified\Tests\Functional\AssertStringContainsTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class AccountTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use ShopRegistrationTrait;
    use AssertStringContainsTrait;

    public function testCanBeCreated()
    {
        $subscriber = $this->getSubscriber();
        static::assertNotNull($subscriber);
    }

    public function testGetSubscribedEventsHasCorrectEvents()
    {
        $events = Account::getSubscribedEvents();
        static::assertCount(1, $events);
        static::assertSame('onPostDispatchAccount', $events['Enlight_Controller_Action_PostDispatchSecure_Frontend_Account']);
    }

    public function testOnPostDispatchAccountIsWrongAction()
    {
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('fooBar');

        $eventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);
        $customerData = $view->getAssign('sUserData');
        static::assertSame('PayPal', $customerData['additional']['payment']['description']);
    }

    public function testOnPostDispatchAccountPaymentMethodInactive()
    {
        $paymentMethodProvider = new PaymentMethodProvider(
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('models')
        );
        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $eventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);
        $customerData = $view->getAssign('sUserData');
        static::assertSame('PayPal', $customerData['additional']['payment']['description']);

        $paymentMethodProvider->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);
    }

    public function testOnPostDispatchAccountNoSettings()
    {
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $eventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);
        $customerData = $view->getAssign('sUserData');
        static::assertSame('PayPal', $customerData['additional']['payment']['description']);
    }

    public function testOnPostDispatchAccountPlusNotActive()
    {
        $subscriber = $this->getSubscriber();

        $this->addSettings(false);

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $eventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);
        $customerData = $view->getAssign('sUserData');
        static::assertSame('PayPal', $customerData['additional']['payment']['description']);
    }

    public function testOnPostDispatchAccountEmptyString()
    {
        $subscriber = $this->getSubscriber();

        $this->addSettings(true, '');

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $eventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);
        $customerData = $view->getAssign('sUserData');
        static::assertSame('PayPal', $customerData['additional']['payment']['description']);
    }

    public function testOnPostDispatchAccountCustomerPayment()
    {
        $this->insertGeneralSettingsFromArray(['active' => true]);
        $this->insertPlusSettingsFromArray([
            'shopId' => 1,
            'active' => true,
            'paymentName' => 'PayPal, Lastschrift oder Kreditkarte',
            'paymentDescription' => '<br>Zahlung per Lastschrift oder Kreditkarte ist auch ohne PayPal Konto möglich',
        ]);

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $eventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $subscriber = $this->getSubscriber();
        $subscriber->onPostDispatchAccount($eventArgs);
        $customerData = $view->getAssign('sUserData');

        static::assertSame('PayPal, Lastschrift oder Kreditkarte', $customerData['additional']['payment']['description']);
        static::assertStringContains(
            $this,
            '<br>Zahlung per Lastschrift oder Kreditkarte ist auch ohne PayPal Konto möglich',
            $customerData['additional']['payment']['additionaldescription']
        );
    }

    public function testOnPostDispatchAccountPaymentMethods()
    {
        $subscriber = $this->getSubscriber();

        $this->addSettings();

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );
        $view->assign($this->getAccountViewAssigns());

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $eventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
        ]);

        $subscriber->onPostDispatchAccount($eventArgs);

        $paymentMethods = $view->getAssign('sPaymentMeans');

        $unifiedPayment = null;
        foreach ($paymentMethods as $paymentMethod) {
            if ((int) $paymentMethod['id'] === 7) {
                $unifiedPayment = $paymentMethod;
            }
        }

        static::assertNotNull($unifiedPayment);
        static::assertSame('PayPal, Lastschrift oder Kreditkarte', $unifiedPayment['description']);
        static::assertStringContains(
            $this,
            '<br>Zahlung per Lastschrift oder Kreditkarte ist auch ohne PayPal Konto möglich',
            $unifiedPayment['additionaldescription']
        );
    }

    /**
     * @return Account
     */
    private function getSubscriber()
    {
        return new Account(
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('paypal_unified.dependency_provider'),
            $this->getContainer()->get('paypal_unified.payment_method_provider')
        );
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
