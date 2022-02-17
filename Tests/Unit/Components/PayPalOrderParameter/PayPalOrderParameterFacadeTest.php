<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    public function createPayPalOrderParameterDefaultDataProvider()
    {
        return [
            [
                PaymentType::PAYPAL_CLASSIC_V2,
                $this->getShopwareOrderData(),
            ],
        ];
    }

    protected function getPaymentControllerHelper()
    {
        $paymentControllerHelper = static::createMock(PaymentControllerHelper::class);

        $paymentControllerHelper->method('setGrossPriceFallback')
            ->willReturnArgument(0);

        return $paymentControllerHelper;
    }

    protected function getDependencyProvider()
    {
        $dependencyProvider = static::createMock(DependencyProvider::class);

        $session = static::createMock(Enlight_Components_Session_Namespace::class);
        $session->method('get')
            ->willReturnMap([
                ['sUserId', null, self::USER_ID],
            ]);

        $dependencyProvider->method('getSession')
            ->willReturn($session);

        return $dependencyProvider;
    }

    protected function getCartPersister()
    {
        $cartPersister = static::createMock(CartPersister::class);

        return $cartPersister;
    }

    /**
     * @param array|null $userData
     * @param array|null $basketData
     *
     * @return MockObject|ShopwareOrderData
     */
    protected function getShopwareOrderData($userData = [], $basketData = [])
    {
        return static::createConfiguredMock(ShopwareOrderData::class, [
            'getShopwareUserData' => $userData,
            'getShopwareBasketData' => $basketData,
        ]);
    }
}
