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
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\EmptyCartException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InstrumentDeclinedException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InvalidBillingAddressException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InvalidShippingAddressException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\NoOrderToProceedException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\PayerActionRequiredException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\PendingException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\RequireRestartException;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use Symfony\Component\HttpFoundation\Response;

class Shopware_Controllers_Frontend_PaypalUnifiedV2 extends AbstractPaypalPaymentController
{
    public function preDispatch()
    {
        parent::preDispatch();

        $this->logger->debug(__METHOD__);

        if ($this->Request()->isXmlHttpRequest()) {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
            $this->Front()->Plugins()->Json()->setRenderer();
            $this->View()->setTemplate();

            $this->logger->debug(\sprintf('%s %s', __METHOD__, 'IS XHR REQUEST'));
        }
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->logger->debug(\sprintf('%s START', __METHOD__));

        $session = $this->dependencyProvider->getSession();
        $shopwareSessionOrderData = $session->get('sOrderVariables');

        $this->handleComment();
        $this->handleNewsletter();

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

        $isPayLater = (bool) $this->Request()->getParam('paypalUnifiedPayLater', false);
        $shopwareOrderData = new ShopwareOrderData($shopwareSessionOrderData['sUserData'], $shopwareSessionOrderData['sBasket']);

        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(
            $isPayLater ? PaymentType::PAYPAL_PAY_LATER : PaymentType::PAYPAL_CLASSIC_V2,
            $shopwareOrderData
        );

        try {
            $payPalOrder = $this->createPayPalOrder($orderParams);
        } catch (InvalidBillingAddressException $invalidBillingAddressException) {
            $this->response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
            $this->view->assign('redirectTo', $this->getInvalidAddressUrl(['invalidBillingAddress' => true]));

            return;
        } catch (InvalidShippingAddressException $invalidShippingAddressException) {
            $this->response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
            $this->view->assign('redirectTo', $this->getInvalidAddressUrl(['invalidShippingAddress' => true]));

            return;
        } catch (EmptyCartException $emptyCartException) {
            $this->response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
            $this->view->assign('redirectTo', $this->getEmptyCartErrorUrl());

            return;
        }

        if (!$payPalOrder instanceof Order) {
            return;
        }

        $this->view->assign('token', $payPalOrder->getId());
        $this->view->assign('basketId', $orderParams->getBasketUniqueId());

        $this->logger->debug(\sprintf('%s END WITH PAYPAL ORDER ID: %s', __METHOD__, $payPalOrder->getId()));
    }

    /**
     * @return void
     */
    public function returnAction()
    {
        $this->logger->debug(\sprintf('%s START', __METHOD__));

        $this->handleComment();

        $payPalOrderId = $this->getPayPalOrderIdFromRequest();
        if ($payPalOrderId === null) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::NO_ORDER_TO_PROCESS)
                ->setException(
                    new UnexpectedValueException(
                        'Cannot get token (PayPalOrderId) from request',
                        ErrorCodes::NO_ORDER_TO_PROCESS
                    ),
                    \sprintf('%s try to get token (PayPalOrderId) from request', __METHOD__)
                );

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $payPalOrder = $this->getPayPalOrder($payPalOrderId);
        if (!$payPalOrder instanceof Order) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::NO_ORDER_TO_PROCESS);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        if (!$this->isCartValid($payPalOrder)) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::BASKET_VALIDATION_ERROR);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        try {
            $payPalOrder = $this->captureOrAuthorizeOrder($payPalOrder);
        } catch (RequireRestartException $requireRestartException) {
            $this->logger->debug(\sprintf('%s REQUIRES A RESTART', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'PaypalUnifiedV2',
                'action' => 'return',
                'token' => $payPalOrderId,
            ]);

            return;
        } catch (PayerActionRequiredException $payerActionRequiredException) {
            $this->logger->debug(\sprintf('%s PAYER_ACTION_REQUIRED', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'confirm',
                'payerActionRequired' => true,
            ]);

            return;
        } catch (InstrumentDeclinedException $instrumentDeclinedException) {
            $this->logger->debug(\sprintf('%s INSTRUMENT_DECLINED', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'confirm',
                'payerInstrumentDeclined' => true,
            ]);

            return;
        } catch (NoOrderToProceedException $noOrderToProceedException) {
            $this->orderNumberService->restoreOrdernumberToPool();

            return;
        }

        try {
            if (!$this->checkCaptureAuthorizationStatus($payPalOrder)) {
                $this->orderNumberService->restoreOrdernumberToPool();

                return;
            }
        } catch (PendingException $capturePendingException) {
            $this->handlePendingOrder($payPalOrder);

            return;
        }

        if ($this->checkIfTransactionIdIsAlreadyAssigned($payPalOrder)) {
            $this->orderNumberService->restoreOrdernumberToPool();

            return;
        }

        $shopwareOrderNumber = $this->createShopwareOrder($payPalOrder->getId(), $this->getPaymentType());

        $this->setTransactionId($shopwareOrderNumber, $payPalOrder);

        $this->updatePaymentStatus($payPalOrder->getIntent(), $this->getOrderId($shopwareOrderNumber));

        $this->logger->debug(\sprintf('%s REDIRECT TO checkout/finish', __METHOD__));

        $this->redirect([
            'module' => 'frontend',
            'controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $payPalOrderId,
        ]);
    }

    /**
     * @return string|null
     */
    private function getPayPalOrderIdFromRequest()
    {
        $payPalOrderID = $this->Request()->getParam('token');
        if ($payPalOrderID !== null) {
            return (string) $payPalOrderID;
        }

        return null;
    }
}
