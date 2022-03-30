<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

class Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout extends AbstractPaypalPaymentController
{
    public function preDispatch()
    {
        parent::preDispatch();

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer();
        $this->View()->setTemplate();
    }

    /**
     * @return void
     */
    public function createOrderAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        //If the PayPal express button on the detail page was clicked, the addProduct equals true.
        //That means, that it has to be added manually to the basket.
        if ($this->Request()->getParam('addProduct') !== null) {
            $this->addProductToCart();
        }

        $checkoutController = $this->prepareCheckoutController();
        $basketData = $checkoutController->getBasket();
        $userData = $checkoutController->getUserData() ?: [];

        $shopwareOrderData = new ShopwareOrderData($userData, $basketData);
        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2, $shopwareOrderData);

        $payPalOrder = $this->createPayPalOrder($orderParams);
        if (!$payPalOrder instanceof Order) {
            return;
        }

        $this->View()->assign('paypalOrderId', $payPalOrder->getId());
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

        $this->logger->debug(sprintf('%s CREATE NEW CUSTOMER FOR PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));
        $customerService->createNewCustomer($payPalOrder);

        $this->view->assign([
            'expressCheckout' => true,
            'paypalOrderId' => $payPalOrder->getId(),
        ]);
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
        $quantity = (int) $request->getParam('productQuantity');

        $this->logger->debug(sprintf('%s DELETE BASKET', __METHOD__));

        $basketModule->sDeleteBasket();

        $this->logger->debug(sprintf('%s ADD PRODUCT WITH NUMBER: %s AND QUANTITY: %d', __METHOD__, $productNumber, $quantity));

        $basketModule->sAddArticle($request->getParam('productNumber'), $quantity);

        // add potential discounts or surcharges to prevent an amount mismatch
        // on patching the new amount after the confirmation.
        // only necessary if the customer directly checks out from product detail page
        /** @var sAdmin $admin */
        $admin = $this->dependencyProvider->getModule('admin');
        $countries = $admin->sGetCountryList();
        $admin->sGetPremiumShippingcosts(\reset($countries));

        $this->logger->debug(sprintf('%s REFRESH BASKET', __METHOD__));

        $basketModule->sRefreshBasket();

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
