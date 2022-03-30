<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\PayPalOrderParameter;

use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Services\Common\CartPersister;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;

/**
 * @phpstan-import-type CheckoutBasketArray from \Shopware_Controllers_Frontend_Checkout
 */
class PayPalOrderParameterFacade implements PayPalOrderParameterFacadeInterface
{
    /**
     * @var PaymentControllerHelper
     */
    private $paymentControllerHelper;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var CartPersister
     */
    private $cartPersister;

    public function __construct(
        PaymentControllerHelper $paymentControllerHelper,
        DependencyProvider $dependencyProvider,
        CartPersister $cartPersister
    ) {
        $this->paymentControllerHelper = $paymentControllerHelper;
        $this->dependencyProvider = $dependencyProvider;
        $this->cartPersister = $cartPersister;
    }

    /**
     * {@inheritDoc}
     */
    public function createPayPalOrderParameter($paymentType, ShopwareOrderData $shopwareOrderData)
    {
        $session = $this->dependencyProvider->getSession();

        $userData = $this->paymentControllerHelper->setGrossPriceFallback($shopwareOrderData->getShopwareUserData());
        /** @phpstan-var CheckoutBasketArray $cartData */
        $cartData = $shopwareOrderData->getShopwareBasketData();

        $basketUniqueId = $this->cartPersister->persist($cartData, $session->get('sUserId'));
        $paymentToken = $this->dependencyProvider->createPaymentToken();

        return new PayPalOrderParameter($userData, $cartData, $paymentType, $basketUniqueId, $paymentToken);
    }
}
