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
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacade;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrderBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;

class Shopware_Controllers_Widgets_PaypalUnifiedV2SmartPaymentButtons extends Shopware_Controllers_Frontend_Payment
{
    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var \Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var PayPalOrderBuilderService
     */
    private $orderBuilderService;

    /**
     * @var RedirectDataBuilderFactory
     */
    private $redirectDataBuilderFactory;

    /**
     * @var PaymentControllerHelper
     */
    private $paymentControllerHelper;

    /**
     * @var PayPalOrderParameterFacade
     */
    private $payPalOrderParameterFacade;

    public function preDispatch()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer();
        $this->view->setTemplate();

        $this->orderBuilderService = $this->get('paypal_unified.paypal_order_builder_service');
        $this->orderResource = $this->get('paypal_unified.v2.order_resource');
        $this->redirectDataBuilderFactory = $this->get('paypal_unified.redirect_data_builder_factory');
        $this->paymentControllerHelper = $this->get('paypal_unified.payment_controller_helper');
        $this->dependencyProvider = $this->get('paypal_unified.dependency_provider');
        $this->shopwareConfig = $this->get('config');
        $this->payPalOrderParameterFacade = $this->get('paypal_unified.paypal_order_parameter_facade');
    }

    public function createOrderAction()
    {
        $session = $this->dependencyProvider->getSession();
        $shopwareOrderData = $session->get('sOrderVariables');

        if ($shopwareOrderData === null) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::NO_ORDER_TO_PROCESS);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        if ($this->noDispatchForOrder()) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::NO_DISPATCH_FOR_ORDER);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2, $shopwareOrderData);
        $payPalOrderData = $this->orderBuilderService->getOrder($orderParams);

        try {
            $payPalOrder = $this->orderResource->create($payPalOrderData, PartnerAttributionId::PAYPAL_ALL_V2, false);
        } catch (RequestException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        } catch (Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $this->view->assign('token', $payPalOrder->getId());
        $this->view->assign('basketId', $orderParams->getBasketUniqueId());
    }

    /**
     * @return bool
     */
    private function noDispatchForOrder()
    {
        $session = $this->dependencyProvider->getSession();

        return !empty($this->shopwareConfig->get('premiumShippingNoOrder')) && (empty($session->get('sDispatch')) || empty($session->get('sCountry')));
    }
}
