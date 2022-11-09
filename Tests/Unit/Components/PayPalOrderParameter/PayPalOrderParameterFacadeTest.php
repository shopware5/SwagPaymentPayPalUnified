<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\PayPalOrderParameter;

use Enlight_Components_Session_Namespace;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacade;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Components\Services\Common\CartPersister;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

class PayPalOrderParameterFacadeTest extends TestCase
{
    const USER_ID = 3076905372468594262;

    /**
     * @dataProvider createPayPalOrderParameterDefaultDataProvider
     *
     * @param PaymentType::*    $paymentType
     * @param ShopwareOrderData $shopwareOrderData
     *
     * @return void
     */
    public function testFacadeSetsGrossPriceFallback($paymentType, $shopwareOrderData)
    {
        $paymentControllerHelper = $this->getPaymentControllerHelper();

        // Assert the gross price fallback is set for the user data passed
        $paymentControllerHelper->expects(static::once())
            ->method('setGrossPriceFallback')
            ->with($shopwareOrderData->getShopwareUserData());

        $subject = new PayPalOrderParameterFacade(
            $paymentControllerHelper,
            $this->getDependencyProvider(),
            $this->getCartPersister()
        );

        $subject->createPayPalOrderParameter($paymentType, $shopwareOrderData);
    }

    /**
     * @dataProvider createPayPalOrderParameterDefaultDataProvider
     *
     * @param PaymentType::*    $paymentType
     * @param ShopwareOrderData $shopwareOrderData
     *
     * @return void
     */
    public function testFacadeGeneratesBasketUniqueId($paymentType, $shopwareOrderData)
    {
        $cartPersister = $this->getCartPersister();

        // Assert the unique ID is created using the correct data
        $cartPersister->expects(static::once())
            ->method('persist')
            ->with($shopwareOrderData->getShopwareBasketData(), self::USER_ID);

        $subject = new PayPalOrderParameterFacade(
            $this->getPaymentControllerHelper(),
            $this->getDependencyProvider(),
            $cartPersister
        );

        $subject->createPayPalOrderParameter($paymentType, $shopwareOrderData);
    }

    /**
     * @return array<array{0: PaymentType::PAYPAL_CLASSIC_V2, 1: ShopwareOrderData}>
     */
    public function createPayPalOrderParameterDefaultDataProvider()
    {
        return [
            [
                PaymentType::PAYPAL_CLASSIC_V2,
                $this->createConfiguredMock(ShopwareOrderData::class, [
                    'getShopwareUserData' => [],
                    'getShopwareBasketData' => [],
                ]),
            ],
        ];
    }

    /**
     * @return PaymentControllerHelper|MockObject
     */
    private function getPaymentControllerHelper()
    {
        $paymentControllerHelper = $this->createMock(PaymentControllerHelper::class);

        $paymentControllerHelper->method('setGrossPriceFallback')
            ->willReturnArgument(0);

        return $paymentControllerHelper;
    }

    /**
     * @return DependencyProvider
     */
    private function getDependencyProvider()
    {
        $dependencyProvider = $this->createMock(DependencyProvider::class);

        $session = $this->createMock(Enlight_Components_Session_Namespace::class);
        $session->method('get')
            ->willReturnMap([
                ['sUserId', null, self::USER_ID],
            ]);

        $dependencyProvider->method('getSession')
            ->willReturn($session);

        return $dependencyProvider;
    }

    /**
     * @return CartPersister|MockObject
     */
    private function getCartPersister()
    {
        return $this->createMock(CartPersister::class);
    }
}
