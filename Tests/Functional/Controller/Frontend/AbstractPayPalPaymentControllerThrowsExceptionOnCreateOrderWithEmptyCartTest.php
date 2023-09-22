<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Controller_Action;
use Enlight_Controller_Response_ResponseHttp;
use Generator;
use Shopware_Controllers_Frontend_PaypalUnifiedApm;
use Shopware_Controllers_Frontend_PaypalUnifiedV2;
use Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice;
use Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard;
use Shopware_Controllers_Widgets_PaypalUnifiedV2SmartPaymentButtons;
use SwagPaymentPayPalUnified\Tests\Functional\AssertLocationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPayPalPaymentControllerThrowsExceptionOnCreateOrderWithEmptyCartTest extends PaypalPaymentControllerTestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use AssertLocationTrait;

    /**
     * @dataProvider createPayPalOrderShouldThrowExceptionWithEmptyCartDataProvider
     *
     * @param class-string<Enlight_Controller_Action> $controllerClass
     * @param string                                  $actionName
     *
     * @return void
     */
    public function testCreatePayPalOrderShouldThrowExceptionWithEmptyCart($controllerClass, $actionName, bool $shouldAssignToView)
    {
        $sOrderVariables = [
            'sUserData' => require __DIR__ . '/_fixtures/getUser_result.php',
            'sBasket' => ['content' => []],
        ];

        $session = $this->getContainer()->get('session');
        $session->offsetSet('sOrderVariables', $sOrderVariables);

        $controller = $this->getController(
            $controllerClass,
            [
                self::SERVICE_DEPENDENCY_PROVIDER => $this->getContainer()->get('paypal_unified.dependency_provider'),
                self::SERVICE_ORDER_PARAMETER_FACADE => $this->getContainer()->get('paypal_unified.paypal_order_parameter_facade'),
            ],
            null,
            new Enlight_Controller_Response_ResponseHttp()
        );

        $controller->$actionName();

        if ($shouldAssignToView) {
            $result = $controller->View()->getAssign('redirectTo');

            static::assertStringEndsWith('checkout/cart', $result);

            return;
        }

        static::assertLocationEndsWith($controller->Response(), 'checkout/cart');
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function createPayPalOrderShouldThrowExceptionWithEmptyCartDataProvider()
    {
        yield 'Shopware_Controllers_Frontend_PaypalUnifiedV2' => [
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            'indexAction',
            true,
        ];

        yield 'Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard' => [
            Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard::class,
            'createOrderAction',
            true,
        ];

        yield 'Shopware_Controllers_Widgets_PaypalUnifiedV2SmartPaymentButtons' => [
            Shopware_Controllers_Widgets_PaypalUnifiedV2SmartPaymentButtons::class,
            'createOrderAction',
            true,
        ];

        yield 'Shopware_Controllers_Frontend_PaypalUnifiedApm' => [
            Shopware_Controllers_Frontend_PaypalUnifiedApm::class,
            'indexAction',
            false,
        ];

        yield 'Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice' => [
            Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice::class,
            'indexAction',
            false,
        ];
    }
}
