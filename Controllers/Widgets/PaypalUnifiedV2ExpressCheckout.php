<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Components\Services\ExpressCheckout\PatchOrderService;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderPurchaseUnitShippingAddressPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderPurchaseUnitShippingNamePatch;

class Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout extends AbstractPaypalPaymentController
{
    /**
     * @var PatchOrderService
     */
    private $patchOrderService;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer();
        $this->View()->setTemplate();

        $this->patchOrderService = $this->container->get('paypal_unified.express_checkout.patch_service');
    }

    /**
     * @return void
     */
    public function createOrderAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $paymentId = $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        // Set PayPal as paymentMethod
        $this->dependencyProvider->getSession()->offsetSet('sPaymentID', $paymentId);

        $checkoutController = $this->prepareCheckoutController();

        $basketData = $checkoutController->getBasket();
        $userData = $checkoutController->getUserData() ?: [];
        if (!empty($basketData['content'])) {
            if ($this->dependencyProvider->getModule('admin')->sManageRisks($paymentId, $basketData, $userData)) {
                $this->View()->assign('riskManagementFailed', true);

                return;
            }
        }

        // If the PayPal express button on the detail page was clicked, the addProduct equals true.
        // That means, that it has to be added manually to the basket.
        if ($this->Request()->getParam('addProduct') !== null) {
            $this->addProductToCart();
            // Make sure, that country and shipping method are set in the session before getting the cart,
            // to ensure that the shipping costs are sent to PayPal
            $checkoutController->getSelectedCountry();
            $checkoutController->getSelectedDispatch();
        }

        $basketData = $checkoutController->getBasket();
        $userData = $checkoutController->getUserData() ?: [];

        $shopwareOrderData = new ShopwareOrderData($userData, $basketData);
        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2, $shopwareOrderData);

        $payPalOrder = $this->createPayPalOrder($orderParams);
        if (!$payPalOrder instanceof Order) {
            return;
        }

        $this->View()->assign('token', $payPalOrder->getId());
    }

    /**
     * @return void
     */
    public function onApproveAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $payPalOrderId = $this->Request()->get('orderID');

        $payPalOrder = $this->getPayPalOrder($payPalOrderId);
        if (!$payPalOrder instanceof Order) {
            return;
        }

        $customerService = $this->get('paypal_unified.express_checkout.customer_service');
        $customerService->upsertCustomer($payPalOrder);

        $this->view->assign([
            'expressCheckout' => true,
            'token' => $payPalOrder->getId(),
        ]);
    }

    /**
     * @return void
     */
    public function patchAddressAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $payPalOrderId = $this->request->getParam('token');
        if (!\is_string($payPalOrderId)) {
            $this->logger->warning(sprintf('%s REQUIRED REQUEST PARAMETER "token" NOT FOUND', __METHOD__));

            return;
        }

        $patches = [];
        $userData = $this->getUser() ?: [];
        $cartData = $this->getBasket() ?: [];

        $shippingAddressPatch = $this->patchOrderService->createExpressShippingAddressPatch($userData, $cartData);
        if ($shippingAddressPatch instanceof OrderPurchaseUnitShippingAddressPatch) {
            $patches[] = $shippingAddressPatch;
        } else {
            $this->logger->debug(sprintf('%s COULD NOT CREATE ADDRESS PATCH FOR PAYPAL ORDER: %s', __METHOD__, $payPalOrderId));
        }

        $shippingNamePatch = $this->patchOrderService->createExpressShippingNamePatch($userData);

        if ($shippingNamePatch instanceof OrderPurchaseUnitShippingNamePatch) {
            $patches[] = $shippingNamePatch;
        } else {
            $this->logger->debug(sprintf('%s COULD NOT CREATE NAME PATCH FOR PAYPAL ORDER: %s', __METHOD__, $payPalOrderId));
        }

        $this->patchOrderService->patchPayPalExpressOrder($patches, $payPalOrderId);

        $this->logger->debug(sprintf('%s END', __METHOD__));
    }

    /**
     * @return void
     */
    private function addProductToCart()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        /** @var sBasket $basketModule */
        $basketModule = $this->dependencyProvider->getModule('basket');
        $request = $this->Request();
        $productNumber = $request->getParam('productNumber');
        $quantity = (int) $request->getParam('productQuantity', 1);

        $this->logger->debug(sprintf('%s DELETE BASKET', __METHOD__));

        $basketModule->sDeleteBasket();

        $this->logger->debug(sprintf('%s ADD PRODUCT WITH NUMBER: %s AND QUANTITY: %d', __METHOD__, $productNumber, $quantity));

        $basketModule->sAddArticle($productNumber, $quantity);

        // add potential discounts or surcharges to prevent an amount mismatch
        // on patching the new amount after the confirmation.
        // only necessary if the customer directly checks out from product detail page
        /** @var sAdmin $admin */
        $admin = $this->dependencyProvider->getModule('admin');
        $countries = $admin->sGetCountryList();
        $country = \reset($countries);
        if (!\is_array($country)) {
            $country = null;
        }

        $admin->sGetPremiumShippingcosts($country);

        $this->logger->debug(sprintf('%s REFRESH BASKET', __METHOD__));

        $basketModule->sGetBasket();

        $this->logger->debug(sprintf('%s PRODUCT SUCCESSFUL ADDED', __METHOD__));
    }

    /**
     * @return Shopware_Controllers_Frontend_Checkout
     */
    private function prepareCheckoutController()
    {
        /** @var Shopware_Controllers_Frontend_Checkout $checkoutController */
        $checkoutController = Enlight_Class::Instance(Shopware_Controllers_Frontend_Checkout::class, [$this->request, $this->response]);
        $checkoutController->init();
        $checkoutController->setView($this->View());
        $checkoutController->setContainer($this->container);
        $checkoutController->setFront($this->front);

        return $checkoutController;
    }
}
