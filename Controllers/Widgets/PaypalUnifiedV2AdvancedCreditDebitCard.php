<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception\ThreeDSecureAuthorizationCanceledException;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception\ThreeDSecureAuthorizationFailedException;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception\ThreeDSecureAuthorizationRejectedException;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\Exception\ThreeDSecureCardHasNoAuthorization;
use SwagPaymentPayPalUnified\Components\Services\ThreeDSecureResultChecker\ThreeDSecureResultChecker;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
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

class Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard extends AbstractPaypalPaymentController
{
    const MAX_THREE_D_SECURE_RETRIES = 3;

    const THREE_D_SECURE_RETRY_WAITING_TIME = 2;

    /**
     * @var ThreeDSecureResultChecker
     */
    protected $threeDSecureResultChecker;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->threeDSecureResultChecker = $this->get('paypal_unified.three_d_secure_result_checker');

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

        $shopwareOrderData = new ShopwareOrderData($shopwareSessionOrderData['sUserData'], $shopwareSessionOrderData['sBasket']);
        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD, $shopwareOrderData);

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
        }

        if (!$payPalOrder instanceof Order) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::NO_ORDER_TO_PROCESS);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $this->view->assign('token', $payPalOrder->getId());
    }

    /**
     * @return void
     */
    public function captureAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $payPalOrderId = $this->request->getParam('token');
        $threeDSecureRetry = (int) $this->request->getParam('threeDSecureRetry', 0);

        if (!\is_string($payPalOrderId)) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException(new UnexpectedValueException("Required request parameter 'token' (paypalOrderId) is missing"), '');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $payPalOrder = $this->getPayPalOrder($payPalOrderId);
        if (!$payPalOrder instanceof Order) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        try {
            $this->threeDSecureResultChecker->checkStaus($payPalOrder);
        } catch (ThreeDSecureAuthorizationFailedException $threeDSecureAuthorizationFailedException) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode($threeDSecureAuthorizationFailedException->getCode())
                ->setException($threeDSecureAuthorizationFailedException, 'captureAction: ThreeDSecure authorization aborted or failed');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        } catch (ThreeDSecureAuthorizationRejectedException $threeDSecureAuthorizationRejectedException) {
            if ($threeDSecureRetry < self::MAX_THREE_D_SECURE_RETRIES) {
                sleep(self::THREE_D_SECURE_RETRY_WAITING_TIME);

                $this->redirect([
                    'module' => 'widgets',
                    'controller' => 'PaypalUnifiedV2AdvancedCreditDebitCard',
                    'action' => 'capture',
                    'token' => $payPalOrderId,
                    'threeDSecureRetry' => ++$threeDSecureRetry,
                ]);

                return;
            }

            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode($threeDSecureAuthorizationRejectedException->getCode())
                ->setException($threeDSecureAuthorizationRejectedException, 'captureAction: ThreeDSecure authorization rejected');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        } catch (ThreeDSecureAuthorizationCanceledException $authorizationCanceledException) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode($authorizationCanceledException->getCode())
                ->setException($authorizationCanceledException, 'captureAction: ThreeDSecure authorization canceled');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        } catch (ThreeDSecureCardHasNoAuthorization $hasNoAuthorizationException) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode($hasNoAuthorizationException->getCode())
                ->setException($hasNoAuthorizationException, 'captureAction: No ThreeDSecure');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        } catch (Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode($exception->getCode())
                ->setException($exception, 'captureAction: ThreeDSecure unknown authorization failure');

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
            $this->logger->debug(sprintf('%s REQUIRES A RESTART', __METHOD__));

            $this->redirect([
                'module' => 'widgets',
                'controller' => 'PaypalUnifiedV2AdvancedCreditDebitCard',
                'action' => 'capture',
                'token' => $payPalOrderId,
            ]);

            return;
        } catch (PayerActionRequiredException $payerActionRequiredException) {
            $this->logger->debug(sprintf('%s PAYER_ACTION_REQUIRED', __METHOD__));

            $this->Response()->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
            $redirectUrl = $this->dependencyProvider->getRouter()->assemble([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'confirm',
                'payerActionRequired' => true,
            ]);

            $this->view->assign('redirectTo', $redirectUrl);

            return;
        } catch (InstrumentDeclinedException $instrumentDeclinedException) {
            $this->logger->debug(sprintf('%s INSTRUMENT_DECLINED', __METHOD__));

            $this->Response()->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
            $redirectUrl = $this->dependencyProvider->getRouter()->assemble([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'confirm',
                'payerInstrumentDeclined' => true,
            ]);

            $this->view->assign('redirectTo', $redirectUrl);

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

        $this->logger->debug(sprintf('%s SET PAYPAL ORDER ID TO SESSION: ID: %s', __METHOD__, $payPalOrderId));

        $this->dependencyProvider->getSession()->offsetSet('token', $payPalOrderId);
    }

    /**
     * @return void
     */
    public function errorAction()
    {
        $paypalUnifiedErrorCode = $this->request->getParam('code');

        $this->logger->error(sprintf('%s ERROR WITH CODE: %d', __METHOD__, $paypalUnifiedErrorCode ?: ErrorCodes::UNKNOWN));

        $this->View()->assign('paypalUnifiedErrorCode', $paypalUnifiedErrorCode ?: ErrorCodes::UNKNOWN);
        $this->View()->extendsTemplate($this->container->getParameter('paypal_unified.plugin_dir') . '/Resources/views/frontend/paypal_unified/checkout/error_message.tpl');
    }
}
