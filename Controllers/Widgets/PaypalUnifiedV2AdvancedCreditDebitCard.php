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
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InstrumentDeclinedException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\NoOrderToProceedException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\PayerActionRequiredException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\RequireRestartException;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;

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

        $payPalOrder = $this->getPayPalOrder($payPalOrderId);
        if (!$payPalOrder instanceof Order) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $liabilityShift = $this->getLiabilityShift($payPalOrder);

        if ($liabilityShift !== AuthenticationResult::LIABILITY_SHIFT_POSSIBLE) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::THREE_D_SECURE_CHECK_FAILED)
                ->setException(new UnexpectedValueException(sprintf('Expected liablitiy shift to be "%s", got: %s', AuthenticationResult::LIABILITY_SHIFT_POSSIBLE, $liabilityShift)), '');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        if (!$this->isCartValid($payPalOrder)) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::BASKET_VALIDATION_ERROR);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $result = $this->patchOrderNumber($payPalOrder);
        if (!$result->getSuccess()) {
            $this->orderNumberService->restoreOrdernumberToPool($result->getShopwareOrderNumber());

            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        try {
            $payPalOrder = $this->captureOrAuthorizeOrder($payPalOrder);
        } catch (RequireRestartException $requireRestartException) {
            $this->logger->debug(sprintf('%s REQUIRES A RESTART', __METHOD__));

            $this->orderNumberService->releaseOrderNumber();

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'PaypalUnifiedV2AdvancedCreditDebitCard',
                'action' => 'capture',
                'paypalOrderId' => $payPalOrderId,
            ]);

            return;
        } catch (PayerActionRequiredException $payerActionRequiredException) {
            $this->logger->debug(sprintf('%s PAYER_ACTION_REQUIRED', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'confirm',
                'payerActionRequired' => true,
            ]);

            return;
        } catch (InstrumentDeclinedException $instrumentDeclinedException) {
            $this->logger->debug(sprintf('%s INSTRUMENT_DECLINED', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'confirm',
                'payerInstrumentDeclined' => true,
            ]);

            return;
        } catch (NoOrderToProceedException $noOrderToProceedException) {
            $this->orderNumberService->restoreOrdernumberToPool($result->getShopwareOrderNumber());

            return;
        }

        if (!$this->checkCaptureAuthorizationStatus($payPalOrder)) {
            $this->orderNumberService->restoreOrdernumberToPool($result->getShopwareOrderNumber());

            return;
        }

        $this->logger->debug(sprintf('%s SET PAYPAL ORDER ID TO SESSION: ID: %s', __METHOD__, $payPalOrderId));

        $this->dependencyProvider->getSession()->offsetSet('paypalOrderId', $payPalOrderId);
        $this->dependencyProvider->getSession()->offsetSet(self::ACDC_SHOPWARE_ORDER_ID_SESSION_KEY, $result->getShopwareOrderNumber());
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

    /**
     * @return AuthenticationResult::LIABILITY_SHIFT_*|null
     */
    private function getLiabilityShift(Order $payPalOrder)
    {
        $paymentSource = $payPalOrder->getPaymentSource();

        if (!$paymentSource instanceof PaymentSource) {
            return null;
        }

        $card = $paymentSource->getCard();

        if (!$card instanceof Card) {
            return null;
        }

        $authenticationResult = $card->getAuthenticationResult();

        if (!$authenticationResult instanceof AuthenticationResult) {
            return null;
        }

        return $authenticationResult->getLiabilityShift();
    }
}
