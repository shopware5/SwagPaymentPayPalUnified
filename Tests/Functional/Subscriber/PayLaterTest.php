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
use Enlight_Event_EventArgs;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Subscriber\PayLater;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class PayLaterTest extends TestCase
{
    use ContainerTrait;
    use SettingsHelperTrait;
    use DatabaseTestCaseTrait;
    use ShopRegistrationTrait;

    /**
     * @dataProvider onCheckoutTestDataProvider
     *
     * @param array<string,mixed>      $generalSettings
     * @param array<string,mixed>|null $expectedViewAssign
     *
     * @return void
     */
    public function testAddPayLaterButtonButton(Enlight_Event_EventArgs $eventArgs, array $generalSettings, $expectedViewAssign = null)
    {
        if (!empty($generalSettings)) {
            $this->insertGeneralSettingsFromArray($generalSettings);
        }

        $subscriber = $this->createPayLaterSubscriber();

        $subscriber->addPayLaterButtonButton($eventArgs);

        $viewAssign = $eventArgs->get('subject')->View()->getAssign();
        unset($viewAssign['sPayment']);

        if ($expectedViewAssign === null) {
            static::assertEmpty($viewAssign);
        } else {
            foreach ($expectedViewAssign as $key => $expectedValue) {
                static::assertSame($expectedValue, $viewAssign[$key]);
            }
        }
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function onCheckoutTestDataProvider()
    {
        yield 'Action name does not fit' => [
            $this->createEnlightEventArgs('someActionName'),
            [],
        ];

        yield 'Action name is confirm but param "paypalUnifiedPayLater" is set' => [
            $this->createEnlightEventArgs('confirm', ['paypalUnifiedPayLater' => true]),
            [],
        ];

        yield 'Without any general settings' => [
            $this->createEnlightEventArgs('confirm'),
            [],
        ];

        yield 'With general settings but paypal is switched off' => [
            $this->createEnlightEventArgs('confirm'),
            ['active' => 0],
        ];

        yield 'Payment method does not fit' => [
            $this->createEnlightEventArgs('confirm', [], ['sPayment' => ['name' => 'anyPaymentMethod']]),
            ['active' => 1],
        ];

        yield 'Method should assign pay later data' => [
            $this->createEnlightEventArgs(
                'confirm',
                [],
                ['sPayment' => ['name' => PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME]]
            ),
            ['active' => 1, 'sandbox_client_id' => 'anyClientId'],
            [
                'showPaypalUnifiedPayLaterButton' => true,
                'paypalUnifiedPayLater' => true,
                'paypalUnifiedPayLaterClientId' => 'anyClientId',
                'paypalUnifiedPayLaterCurrency' => 'EUR',
                'paypalUnifiedPayLaterIntent' => 'CAPTURE',
                'paypalUnifiedPayLaterStyleShape' => 'rectangle',
                'paypalUnifiedPayLaterStyleSize' => 'medium',
                'paypalUnifiedPayLaterButtonLocale' => 'de_DE',
            ],
        ];
    }

    /**
     * @dataProvider addInfoToPaymentRequestTestDataProvider
     *
     * @param string              $actionName
     * @param array<string,mixed> $requestParams
     * @param bool                $isAlreadyRedirected
     * @param string              $expectedLocation
     *
     * @return void
     */
    public function testAddInfoToPaymentRequest($actionName, array $requestParams, $isAlreadyRedirected, $expectedLocation)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName($actionName);
        $request->setParams($requestParams);

        $response = new Enlight_Controller_Response_ResponseTestCase();
        if ($isAlreadyRedirected) {
            $response->setRedirect('http://127.0.0.1');
        }

        $controller = new DummyController($request, new ViewMock(new Enlight_Template_Manager()), $response);

        $controllerEventArgs = new Enlight_Controller_ActionEventArgs([
            'request' => $request,
            'response' => $response,
            'subject' => $controller,
        ]);

        $subscriber = $this->createPayLaterSubscriber();

        $subscriber->addInfoToPaymentRequest($controllerEventArgs);

        $this->compareHeader($expectedLocation, $response);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function addInfoToPaymentRequestTestDataProvider()
    {
        yield 'Action name does not match' => [
            'anyActionName',
            ['paypalUnifiedPayLater' => true],
            true,
            'http://127.0.0.1',
        ];

        yield 'Request param paypalUnifiedPayLater is false' => [
            'payment',
            ['paypalUnifiedPayLater' => false],
            true,
            'http://127.0.0.1',
        ];

        yield 'Is not redirected' => [
            'payment',
            ['paypalUnifiedPayLater' => true],
            false,
            '',
        ];

        yield 'All params does not match' => [
            'payment',
            ['paypalUnifiedPayLater' => true, 'paypalOrderId' => 'foo', 'payerId' => 'bar', 'basketId' => '42'],
            true,
            '/PaypalUnifiedV2/return/paypalUnifiedPayLater/1/token/foo/PayerID/bar/basketId/42',
        ];
    }

    /**
     * @dataProvider addPayLaterInfoToRequestTest
     *
     * @param string              $actionName
     * @param array<string,mixed> $requestParams
     * @param bool                $isAlreadyRedirected
     * @param array<string,mixed> $expectedViewAssign
     *
     * @return void
     */
    public function testAddPayLaterInfoToRequest($actionName, array $requestParams, $isAlreadyRedirected, $expectedViewAssign = [])
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName($actionName);
        $request->setParams($requestParams);

        $response = new Enlight_Controller_Response_ResponseTestCase();
        if ($isAlreadyRedirected) {
            $response->setRedirect('http://127.0.0.1');
        }

        $view = new ViewMock(new Enlight_Template_Manager());

        $controller = new DummyController($request, $view, $response);

        $controllerEventArgs = new Enlight_Controller_ActionEventArgs([
            'request' => $request,
            'response' => $response,
            'subject' => $controller,
        ]);

        $subscriber = $this->createPayLaterSubscriber();

        $subscriber->addPayLaterInfoToRequest($controllerEventArgs);

        if (empty($expectedViewAssign)) {
            static::assertTrue(empty($view->getAssign()));

            return;
        }

        static::assertTrue($view->getAssign('paypalUnifiedPayLater'));
        static::assertTrue($view->getAssign('paypalUnifiedPayLaterCheckout'));
        static::assertSame($expectedViewAssign['paypalUnifiedPayLaterOrderId'], $view->getAssign('paypalUnifiedPayLaterOrderId'));
        static::assertSame($expectedViewAssign['paypalUnifiedPayLaterPayerId'], $view->getAssign('paypalUnifiedPayLaterPayerId'));
        static::assertSame($expectedViewAssign['paypalUnifiedPayLaterBasketId'], $view->getAssign('paypalUnifiedPayLaterBasketId'));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function addPayLaterInfoToRequestTest()
    {
        yield 'ActionName is payment' => [
            'payment',
            [],
            false,
        ];

        yield 'Parameter paypalUnifiedPayLater is set' => [
            'anyActionName',
            ['paypalUnifiedPayLater' => true],
            false,
        ];

        yield 'Is already redirected' => [
            'anyActionName',
            [],
            true,
        ];

        yield 'All early return params match' => [
            'payment',
            ['paypalUnifiedPayLater' => true],
            true,
        ];

        yield 'ActionName is confirm and paypalUnifiedPayLater is set' => [
            'confirm',
            ['paypalUnifiedPayLater' => true, 'paypalOrderId' => 'foo', 'payerId' => 'bar', 'basketId' => '42'],
            false,
            [
                'paypalUnifiedPayLaterOrderId' => 'foo',
                'paypalUnifiedPayLaterPayerId' => 'bar',
                'paypalUnifiedPayLaterBasketId' => '42',
            ],
        ];
    }

    /**
     * @param string              $actionName
     * @param array<string,mixed> $requestParams
     * @param array<string,mixed> $viewAssign
     *
     * @return Enlight_Event_EventArgs
     */
    private function createEnlightEventArgs($actionName, array $requestParams = [], array $viewAssign = [])
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName($actionName);
        $request->setParams($requestParams);

        $view = new Enlight_View_Default(new Enlight_Template_Manager());
        $view->assign($viewAssign);

        $subject = new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase());

        $eventArgs = new Enlight_Event_EventArgs();
        $eventArgs->set('subject', $subject);

        return $eventArgs;
    }

    /**
     * @return PayLater
     */
    private function createPayLaterSubscriber()
    {
        return new PayLater(
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('paypal_unified.button_locale_service')
        );
    }

    /**
     * @param string $expectedLocation
     *
     * @return void
     */
    private function compareHeader($expectedLocation, Enlight_Controller_Response_ResponseTestCase $response)
    {
        if (empty($expectedLocation)) {
            static::assertTrue(empty($response->getHeader('Location', '')));

            return;
        }

        if (method_exists(static::class, 'assertStringContainsString')) {
            static::assertStringContainsString(
                $expectedLocation,
                $response->getHeader('Location', '')
            );
        } else {
            static::assertContains(
                $expectedLocation,
                $response->getHeader('Location', '')
            );
        }
    }
}
