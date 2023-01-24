<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\PayPalOrderParameter;

use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\OrderNumberService;
use SwagPaymentPayPalUnified\Components\Services\Common\CartPersister;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

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

    /**
     * @var OrderNumberService
     */
    private $orderNumberService;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    public function __construct(
        PaymentControllerHelper $paymentControllerHelper,
        DependencyProvider $dependencyProvider,
        CartPersister $cartPersister,
        OrderNumberService $orderNumberService,
        SettingsServiceInterface $settingsService
    ) {
        $this->paymentControllerHelper = $paymentControllerHelper;
        $this->dependencyProvider = $dependencyProvider;
        $this->cartPersister = $cartPersister;
        $this->orderNumberService = $orderNumberService;
        $this->settingsService = $settingsService;
    }

    /**
     * {@inheritDoc}
     */
    public function createPayPalOrderParameter($paymentType, ShopwareOrderData $shopwareOrderData)
    {
        $session = $this->dependencyProvider->getSession();
        $userData = $this->paymentControllerHelper->setGrossPriceFallback($shopwareOrderData->getShopwareUserData());
        $cartData = $shopwareOrderData->getShopwareBasketData();

        $basketUniqueId = $this->cartPersister->persist($cartData, $session->get('sUserId'));
        $paymentToken = $this->dependencyProvider->createPaymentToken();

        $orderNumber = $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_ORDER_NUMBER_PREFIX) . $this->orderNumberService->getOrderNumber();

        return new PayPalOrderParameter($userData, $cartData, $paymentType, $basketUniqueId, $paymentToken, $orderNumber);
    }
}
