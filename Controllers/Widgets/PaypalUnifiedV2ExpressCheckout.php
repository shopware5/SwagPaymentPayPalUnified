<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PayPalOrderBuilderParameter;
use SwagPaymentPayPalUnified\Components\Services\Common\CartPersister;
use SwagPaymentPayPalUnified\Components\Services\ExpressCheckout\CustomerService;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrderBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;

class Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout extends Shopware_Controllers_Frontend_Checkout
{
    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var PayPalOrderBuilderService
     */
    private $orderBuilderService;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var RedirectDataBuilderFactory
     */
    private $redirectDataBuilderFactory;

    /**
     * @var PaymentControllerHelper
     */
    private $paymentControllerHelper;

    /**
     * @var CartPersister
     */
    private $cartPersister;

    public function preDispatch()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer();
        $this->View()->setTemplate();

        $this->dependencyProvider = $this->get('paypal_unified.dependency_provider');
        $this->orderBuilderService = $this->get('paypal_unified.paypal_order_builder_service');
        $this->orderResource = $this->get('paypal_unified.v2.order_resource');
        $this->redirectDataBuilderFactory = $this->get('paypal_unified.redirect_data_builder_factory');
        $this->paymentControllerHelper = $this->get('paypal_unified.payment_controller_helper');
        $this->cartPersister = $this->get('paypal_unified.common.cart_persister');
    }

    public function createOrderAction()
    {
        //If the PayPal express button on the detail page was clicked, the addProduct equals true.
        //That means, that it has to be added manually to the basket.
        if ($this->Request()->getParam('addProduct') !== null) {
            $this->addProductToCart();
        }

        $cart = $this->getBasket();
        $userData = $this->getUserData();

        $orderParams = new PayPalOrderBuilderParameter(
            $this->paymentControllerHelper->setGrossPriceFallback($userData),
            $cart,
            PaymentType::PAYPAL_EXPRESS_V2,
            $this->cartPersister->persist($cart, $this->session->get('sUserId')),
            $this->dependencyProvider->createPaymentToken()
        );

        try {
            $payPalOrderData = $this->orderBuilderService->getOrder($orderParams);

            $payPalOrder = $this->orderResource->create($payPalOrderData, PartnerAttributionId::PAYPAL_EXPRESS_CHECKOUT, false);
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

    public function onApproveAction()
    {
        $payPalOrderId = $this->Request()->get('orderID');

        try {
            $payPalOrder = $this->orderResource->get($payPalOrderId);
        } catch (\Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        /** @var CustomerService $customerService */
        $customerService = $this->get('paypal_unified.express_checkout.customer_service');
        $customerService->createNewCustomer($payPalOrder);

        $this->view->assign([
            'expressCheckout' => true,
            'orderId' => $payPalOrder->getId(),
        ]);
    }

    private function addProductToCart()
    {
        /** @var sBasket $basketModule */
        $basketModule = $this->dependencyProvider->getModule('basket');
        $request = $this->Request();

        $basketModule->sDeleteBasket();
        $basketModule->sAddArticle($request->getParam('productNumber'), $request->getParam('productQuantity'));

        // add potential discounts or surcharges to prevent an amount mismatch
        // on patching the new amount after the confirmation.
        // only necessary if the customer directly checks out from product detail page
        /** @var sAdmin $admin */
        $admin = $this->dependencyProvider->getModule('admin');
        $countries = $admin->sGetCountryList();
        $admin->sGetPremiumShippingcosts(\reset($countries));

        $basketModule->sRefreshBasket();
    }
}
