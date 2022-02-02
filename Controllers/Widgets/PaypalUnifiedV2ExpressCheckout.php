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
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacadeInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\ExpressCheckout\CustomerService;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;

/**
 * @phpstan-import-type CheckoutBasketArray from \Shopware_Controllers_Frontend_Checkout
 */
class Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout extends Shopware_Controllers_Frontend_Checkout
{
    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

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
     * @var PayPalOrderParameterFacadeInterface
     */
    private $payPalOrderParameterFacade;

    /**
     * @var ExceptionHandlerService
     */
    private $exceptionHandler;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function preDispatch()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer();
        $this->View()->setTemplate();

        $this->dependencyProvider = $this->get('paypal_unified.dependency_provider');
        $this->orderResource = $this->get('paypal_unified.v2.order_resource');
        $this->redirectDataBuilderFactory = $this->get('paypal_unified.redirect_data_builder_factory');
        $this->paymentControllerHelper = $this->get('paypal_unified.payment_controller_helper');
        $this->payPalOrderParameterFacade = $this->get('paypal_unified.paypal_order_parameter_facade');
        $this->exceptionHandler = $this->get('paypal_unified.exception_handler_service');
        $this->orderFactory = $this->get('paypal_unified.order_factory');
        $this->logger = $this->get('swag_payment_pay_pal_unified.logger');
    }

    public function createOrderAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        //If the PayPal express button on the detail page was clicked, the addProduct equals true.
        //That means, that it has to be added manually to the basket.
        if ($this->Request()->getParam('addProduct') !== null) {
            $this->addProductToCart();
        }

        /** @phpstan-var CheckoutBasketArray $basketData */
        $basketData = $this->getBasket() ?: [];
        $userData = $this->getUserData() ?: [];

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

    public function logErrorMessageAction()
    {
        $code = $this->request->getParam('code', ErrorCodes::UNKNOWN_EXPRESS_ERROR);
        $message = $this->request->getParam('message');

        $this->exceptionHandler->handle(
            new \Exception(sprintf('code: %s%smessage: %s', $code, \PHP_EOL, $message)),
            'API-ERROR'
        );
    }

    private function addProductToCart()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        /** @var sBasket $basketModule */
        $basketModule = $this->dependencyProvider->getModule('basket');
        $request = $this->Request();
        $productNumber = $request->getParam('productNumber');
        $qantity = (int) $request->getParam('productQuantity');

        $this->logger->debug(sprintf('%s DELETE BASKET', __METHOD__));

        $basketModule->sDeleteBasket();

        $this->logger->debug(sprintf('%s ADD PRODUCT WITH NUMBER: %s AND QUANTITY: %d', __METHOD__, $productNumber, $qantity));

        $basketModule->sAddArticle($request->getParam('productNumber'), $qantity);

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
}
