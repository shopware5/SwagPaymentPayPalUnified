<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Event_EventArgs;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Subscriber\PayLaterMessage;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;

class PayLaterMessageTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use ShopRegistrationTrait;

    /**
     * @dataProvider showPayLaterMessageTestDataProvider
     *
     * @param string              $actionName
     * @param array<string,mixed> $settings
     * @param array<string,mixed> $expectedResult
     *
     * @return void
     */
    public function testShowPayLaterMessage($actionName, array $settings, array $expectedResult)
    {
        if (!empty($settings)) {
            $this->insertGeneralSettingsFromArray($settings);
        }

        $subscriber = $this->createPayLaterMessageSubscriber();

        $eventArgs = $this->createEventArgs($actionName);

        $subscriber->showPayLaterMessage($eventArgs);

        $view = $eventArgs->get('subject')->View();
        $result = $view->getAssign('payLaterMesssage');

        static::assertSame($expectedResult['payLaterMesssage'], $result);

        if ($expectedResult['payLaterMesssage'] === true) {
            static::assertNotEmpty($view->getAssign('payLaterMesssageClientId'));
            static::assertNotEmpty($view->getAssign('payLaterMessageCurrency'));
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function showPayLaterMessageTestDataProvider()
    {
        yield 'Action name does not fit' => [
            'anyActionName',
            [],
            ['payLaterMesssage' => null],
        ];

        yield 'Action name is shippingPayment but there are no settings' => [
            'shippingPayment',
            [],
            ['payLaterMesssage' => null],
        ];

        yield 'Action name is payment but paypal is not active' => [
            'payment',
            ['active' => 0, 'sandbox_client_id' => 'anyClientId'],
            ['payLaterMesssage' => null],
        ];

        yield 'Action name is shippingPayment' => [
            'shippingPayment',
            ['active' => 1, 'sandbox_client_id' => 'anyClientId'],
            ['payLaterMesssage' => true],
        ];

        yield 'Action name is payment' => [
            'shippingPayment',
            ['active' => 1, 'sandbox_client_id' => 'anyClientId'],
            ['payLaterMesssage' => true],
        ];
    }

    /**
     * @param string $actionName
     *
     * @return Enlight_Event_EventArgs
     */
    private function createEventArgs($actionName)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName($actionName);

        $view = new Enlight_View_Default(new Enlight_Template_Manager());
        $subject = new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase());

        $eventArgs = new Enlight_Event_EventArgs();
        $eventArgs->set('subject', $subject);

        return $eventArgs;
    }

    /**
     * @return PayLaterMessage
     */
    private function createPayLaterMessageSubscriber()
    {
        return new PayLaterMessage(
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('shopware_storefront.context_service')
        );
    }
}
