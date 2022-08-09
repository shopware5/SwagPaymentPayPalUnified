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
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Subscriber\PayLater;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;

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
}
