<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Components\HttpClient\RequestException;
use Shopware_Controllers_Frontend_PaypalUnifiedV2;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class PaypalUnifiedV2CatchesInvalidAddressExceptionTest extends PaypalPaymentControllerTestCase
{
    use DatabaseTestCaseTrait;
    use ShopRegistrationTrait;
    use SettingsHelperTrait;

    /**
     * @after
     *
     * @return void
     */
    public function clearSession()
    {
        $session = $this->getContainer()->get('session');
        $session->offsetUnset('sOrderVariables');
        $session->offsetUnset('sUserId');
    }

    /**
     * @return void
     */
    public function testIndexActionCatchInvalidBillingAddressException()
    {
        $this->insertGeneralSettingsFromArray(['active' => 1]);

        $this->prepareSession();

        $controller = $this->createController('BILLING_ADDRESS_INVALID');

        $controller->indexAction();

        static::assertStringEndsWith(
            'address/index/invalidBillingAddress/1',
            $controller->View()->getAssign('redirectTo')
        );
    }

    /**
     * @return void
     */
    public function testIndexActionCatchInvalidShippingAddressException()
    {
        $this->insertGeneralSettingsFromArray(['active' => 1]);

        $this->prepareSession();

        $controller = $this->createController('SHIPPING_ADDRESS_INVALID');

        $controller->indexAction();

        static::assertStringEndsWith(
            'address/index/invalidShippingAddress/1',
            $controller->View()->getAssign('redirectTo')
        );
    }

    /**
     * @param string $issue
     *
     * @return Shopware_Controllers_Frontend_PaypalUnifiedV2
     */
    private function createController($issue)
    {
        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            [
                self::SERVICE_DEPENDENCY_PROVIDER => $this->getContainer()->get('paypal_unified.dependency_provider'),
                self::SERVICE_ORDER_PARAMETER_FACADE => $this->getContainer()->get('paypal_unified.paypal_order_parameter_facade'),
                self::SERVICE_ORDER_FACTORY => $this->getContainer()->get('paypal_unified.order_factory'),
                self::SERVICE_PAYMENT_METHOD_PROVIDER => $this->createPaymentMethodProvider(),
                self::SERVICE_ORDER_RESOURCE => $this->createResourceMock($issue),
            ],
            new Enlight_Controller_Request_RequestTestCase(),
            new Enlight_Controller_Response_ResponseTestCase(),
            new Enlight_View_Default(new Enlight_Template_Manager())
        );

        $controller->setFront($this->getContainer()->get('front'));

        return $controller;
    }

    /**
     * @return PaymentMethodProvider&MockObject
     */
    private function createPaymentMethodProvider()
    {
        $paymentMethodProviderMock = $this->createMock(PaymentMethodProvider::class);
        $paymentMethodProviderMock->method('getPaymentTypeByName')->willReturn(PaymentType::APM_GIROPAY);

        return $paymentMethodProviderMock;
    }

    /**
     * @return void
     */
    private function prepareSession()
    {
        $session = $this->getContainer()->get('session');
        $session->offsetSet('sOrderVariables', [
            'sUserData' => require __DIR__ . '/_fixtures/getUser_result.php',
            'sBasket' => require __DIR__ . '/_fixtures/getBasket_result.php',
        ]);
        $session->offsetSet('sUserId', 1);
    }

    /**
     * @param string $issue
     *
     * @return OrderResource&MockObject
     */
    private function createResourceMock($issue)
    {
        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('create')->willThrowException(
            new RequestException(
                'Error',
                0,
                null,
                (string) json_encode(['details' => [['issue' => $issue]]])
            )
        );

        return $orderResourceMock;
    }
}
