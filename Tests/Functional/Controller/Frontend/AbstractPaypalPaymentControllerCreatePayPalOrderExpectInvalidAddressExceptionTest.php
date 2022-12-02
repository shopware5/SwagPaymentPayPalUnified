<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InvalidBillingAddressException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InvalidShippingAddressException;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPaypalPaymentControllerCreatePayPalOrderExpectInvalidAddressExceptionTest extends PaypalPaymentControllerTestCase
{
    use ReflectionHelperTrait;
    use ShopRegistrationTrait;
    use SettingsHelperTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testCreatePayPalOrderShouldThrowInvalidBillingAddressException()
    {
        $this->insertGeneralSettingsFromArray(['active' => 1]);

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('create')->willThrowException(
            new RequestException(
                'Error',
                0,
                null,
                (string) json_encode(['details' => [['issue' => 'BILLING_ADDRESS_INVALID']]])
            )
        );

        $controller = $this->getController(
            AbstractPaypalPaymentController::class,
            [
                self::SERVICE_ORDER_FACTORY => $this->getContainer()->get('paypal_unified.order_factory'),
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
            ]
        );

        $this->expectException(InvalidBillingAddressException::class);
        $this->expectExceptionMessage('Invalid billing address');
        $this->expectExceptionCode(ErrorCodes::ADDRESS_VALIDATION_ERROR);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, 'createPayPalOrder');
        $reflectionMethod->invoke($controller, $this->createOrderParameter());
    }

    /**
     * @return void
     */
    public function testCreatePayPalOrderShouldThrowInvalidShippingAddressException()
    {
        $this->insertGeneralSettingsFromArray(['active' => 1]);

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('create')->willThrowException(
            new RequestException(
                'Error',
                0,
                null,
                (string) json_encode(['details' => [['issue' => 'SHIPPING_ADDRESS_INVALID']]])
            )
        );

        $controller = $this->getController(
            AbstractPaypalPaymentController::class,
            [
                self::SERVICE_ORDER_FACTORY => $this->getContainer()->get('paypal_unified.order_factory'),
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
            ]
        );

        $this->expectException(InvalidShippingAddressException::class);
        $this->expectExceptionMessage('Invalid shipping address');
        $this->expectExceptionCode(ErrorCodes::ADDRESS_VALIDATION_ERROR);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, 'createPayPalOrder');
        $reflectionMethod->invoke($controller, $this->createOrderParameter());
    }

    /**
     * @return PayPalOrderParameter
     */
    private function createOrderParameter()
    {
        $userData = require __DIR__ . '/_fixtures/getUser_result.php';
        $basket = require __DIR__ . '/_fixtures/getBasket_result.php';

        return new PayPalOrderParameter(
            $userData,
            $basket,
            PaymentType::PAYPAL_CLASSIC_V2,
            'anyUniqueId',
            null,
            'anyOrderNumber'
        );
    }
}
