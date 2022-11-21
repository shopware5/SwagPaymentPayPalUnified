<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Components_Session_Namespace;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Exception\BirthdateNotValidException;
use SwagPaymentPayPalUnified\Components\Exception\PhoneNumberCountryCodeNotValidException;
use SwagPaymentPayPalUnified\Components\Exception\PhoneNumberNationalNumberNotValidException;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Tests\Functional\AssertLocationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class PaypalUnifiedV2PayUponInvoiceCatchValidationExceptionsTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;
    use AssertLocationTrait;

    /**
     * @return void
     */
    public function testIndexActionCatchBirthdateNotValidException()
    {
        $controller = $this->createController(new BirthdateNotValidException('2001-ab-ab'));

        $controller->indexAction();

        static::assertSame(302, $controller->Response()->getHttpResponseCode());
        static::assertLocationEndsWith($controller->Response(), 'checkout/confirm/puiBirthdateWrong/1');
    }

    /**
     * @return void
     */
    public function testIndexActionCatchPhoneNumberCountryCodeNotValidException()
    {
        $controller = $this->createController(new PhoneNumberCountryCodeNotValidException('12'));

        $controller->indexAction();

        static::assertSame(302, $controller->Response()->getHttpResponseCode());
        static::assertLocationEndsWith($controller->Response(), 'checkout/confirm/puiPhoneNumberWrong/1');
    }

    /**
     * @return void
     */
    public function testIndexActionCatchPhoneNumberNationalNumberNotValidException()
    {
        $controller = $this->createController(new PhoneNumberNationalNumberNotValidException('12123FooBar'));
        $controller->indexAction();

        static::assertSame(302, $controller->Response()->getHttpResponseCode());
        static::assertLocationEndsWith($controller->Response(), 'checkout/confirm/puiPhoneNumberWrong/1');
    }

    /**
     * @return Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice
     */
    private function createController(Exception $exception)
    {
        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice::class,
            [
                self::SERVICE_ORDER_PARAMETER_FACADE => $this->getContainer()->get('paypal_unified.paypal_order_parameter_facade'),
                self::SERVICE_DEPENDENCY_PROVIDER => $this->createDependencyProviderMock(),
                self::SERVICE_ORDER_FACTORY => $this->createOrderFactoryMock($exception),
            ],
            new Enlight_Controller_Request_RequestTestCase(),
            new Enlight_Controller_Response_ResponseTestCase()
        );

        static::assertInstanceOf(Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice::class, $controller);

        return $controller;
    }

    /**
     * @return OrderFactory&MockObject
     */
    private function createOrderFactoryMock(Exception $exception)
    {
        $orderFactoryMock = $this->createMock(OrderFactory::class);
        $orderFactoryMock->method('createOrder')->willThrowException($exception);

        return $orderFactoryMock;
    }

    /**
     * @return DependencyProvider&MockObject
     */
    private function createDependencyProviderMock()
    {
        $dependencyProviderMock = $this->createMock(DependencyProvider::class);
        $dependencyProviderMock->method('getSession')->willReturn($this->createSessionMock());

        return $dependencyProviderMock;
    }

    /**
     * @return Enlight_Components_Session_Namespace&MockObject
     */
    private function createSessionMock()
    {
        $customerData = require __DIR__ . '/_fixtures/getUser_result.php';
        $basketData = require __DIR__ . '/_fixtures/getBasket_result.php';

        static::assertTrue(\is_array($customerData));
        static::assertTrue(\is_array($basketData));

        $sOrderVariables = [
            'sUserData' => $customerData,
            'sBasket' => $basketData,
        ];

        $sessionMock = $this->createMock(Enlight_Components_Session_Namespace::class);
        $sessionMock->method('offsetGet')->willReturn($sOrderVariables);

        return $sessionMock;
    }
}
