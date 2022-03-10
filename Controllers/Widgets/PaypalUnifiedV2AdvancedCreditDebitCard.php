<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

class Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard extends AbstractPaypalPaymentController
{
    public function preDispatch()
    {
        parent::preDispatch();

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer();
        $this->view->setTemplate();
    }

    /**
     * @return void
     */
    public function createOrderAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $session = $this->dependencyProvider->getSession();
        $shopwareSessionOrderData = $session->get('sOrderVariables');

        if ($shopwareSessionOrderData === null) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::NO_ORDER_TO_PROCESS);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        if ($this->dispatchValidator->isInvalid()) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::NO_DISPATCH_FOR_ORDER);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $shopwareOrderData = new ShopwareOrderData($shopwareSessionOrderData['sUserData'], $shopwareSessionOrderData['sBasket']);
        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD, $shopwareOrderData);

        $payPalOrder = $this->createPayPalOrder($orderParams);
        if (!$payPalOrder instanceof Order) {
            return;
        }

        $this->view->assign('paypalOrderId', $payPalOrder->getId());
    }

    /**
     * @return void
     */
    public function captureAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $payPalOrderId = $this->request->getParam('paypalOrderId');

        if (!\is_string($payPalOrderId)) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException(new UnexpectedValueException("Required request parameter 'paypalOrderId' is missing"), '');
            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $capturedPayPalOrder = $this->captureOrAuthorizeOrder($payPalOrderId);
        if (!$capturedPayPalOrder instanceof Order) {
            return;
        }

        $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD);
        $this->setTransactionId($shopwareOrderNumber, $capturedPayPalOrder);

        $this->logger->debug(sprintf('%s SET PAYPAL ORDER ID TO SESSION: ID: %s', __METHOD__, $payPalOrderId));

        $this->dependencyProvider->getSession()->offsetSet('paypalOrderId', $payPalOrderId);
    }

    /**
     * @return void
     */
    public function errorAction()
    {
        $paypalUnifiedErrorCode = $this->request->getParam('code');

        $this->logger->debug(sprintf('%s ERROR WITH CODE: %d', __METHOD__, $paypalUnifiedErrorCode ?: ErrorCodes::UNKNOWN));

        $this->View()->assign('paypalUnifiedErrorCode', $paypalUnifiedErrorCode ?: ErrorCodes::UNKNOWN);
        $this->View()->extendsTemplate($this->container->getParameter('paypal_unified.plugin_dir') . '/Resources/views/frontend/paypal_unified/checkout/error_message.tpl');
    }
}
