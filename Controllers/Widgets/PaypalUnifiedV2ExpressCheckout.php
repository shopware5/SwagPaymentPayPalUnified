<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Components\Services\ExpressCheckout\CustomerService;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

/**
 * @phpstan-import-type CheckoutBasketArray from \Shopware_Controllers_Frontend_Checkout
 */
class Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout extends AbstractPaypalPaymentController
{
    public function preDispatch()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer();
        $this->View()->setTemplate();

        parent::preDispatch();
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
        /** @phpstan-var CheckoutBasketArray $basketData */
        $basketData = $checkoutController->getBasket();
        $userData = $checkoutController->getUserData() ?: [];

        $shopwareOrderData = new ShopwareOrderData($userData, $basketData);
        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2, $shopwareOrderData);

        try {
            $this->logger->debug(sprintf('%s BEFORE CREATE PAYPAL ORDER', __METHOD__));

            $payPalOrderData = $this->orderFactory->createOrder($orderParams);

            $payPalOrder = $this->orderResource->create($payPalOrderData, $orderParams->getPaymentType(), PartnerAttributionId::PAYPAL_ALL_V2, false);

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFUL CREATED: ID: %d', __METHOD__, $payPalOrder->getId()));
        } catch (RequestException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        } catch (\Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception);
            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $this->View()->assign('orderId', $payPalOrder->getId());
    }

    /**
     * @return void
     */
    public function onApproveAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $payPalOrderId = $this->Request()->get('orderID');

        try {
            $this->logger->debug(sprintf('%s GET PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));

            $payPalOrder = $this->orderResource->get($payPalOrderId);

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY LOADED', __METHOD__));
        } catch (\Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        /** @var CustomerService $customerService */
        $customerService = $this->get('paypal_unified.express_checkout.customer_service');

        $this->logger->debug(sprintf('%s CREATE NEW CUSTOMER FOR PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));
        $customerService->createNewCustomer($payPalOrder);

        $this->view->assign([
            'expressCheckout' => true,
            'orderId' => $payPalOrder->getId(),
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
        $checkoutController = new Shopware_Controllers_Frontend_Checkout();
        $checkoutController->init();
        $checkoutController->setView($this->View());
        $checkoutController->setContainer($this->container);
        $checkoutController->setFront($this->front);

        return $checkoutController;
    }
}
