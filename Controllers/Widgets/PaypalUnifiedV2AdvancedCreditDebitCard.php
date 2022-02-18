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
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

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
        $payPalOrderData = $this->orderFactory->createOrder($orderParams);

        try {
            $this->logger->debug(sprintf('%s BEFORE CREATE PAYPAL ORDER', __METHOD__));

            $payPalOrder = $this->orderResource->create($payPalOrderData, $orderParams->getPaymentType(), PartnerAttributionId::PAYPAL_ALL_V2, false);

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFUL CREATED: ID: %d', __METHOD__, $payPalOrder->getId()));
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

        $this->view->assign('paypalOrderId', $payPalOrder->getId());
    }

    /**
     * @return void
     */
    public function captureAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $paypalOrderId = $this->request->getParam('paypalOrderId');

        if (!$paypalOrderId) {
            throw new UnexpectedValueException('No orderId specified.');
        }

        try {
            $this->logger->debug(sprintf('%s CAPTURE PAYPAL ORDER WITH ID: %s', __METHOD__, $paypalOrderId));

            $paypalOrder = $this->orderResource->capture($paypalOrderId, PartnerAttributionId::PAYPAL_ALL_V2);

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY CAPTURED', __METHOD__));
        } catch (RequestException $exception) {
            $responseBody = json_decode($exception->getBody(), true);
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode($this->getErrorType($responseBody))
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

        $this->createShopwareOrder($paypalOrder->getId(), PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD);

        $this->logger->debug(sprintf('%s SET PAYPAL ORDER ID TO SESSION: ID: %s', __METHOD__, $paypalOrder->getId()));

        $this->dependencyProvider->getSession()->offsetSet('paypalOrderId', $paypalOrder->getId());
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

    /**
     * @param array<mixed> $responseBody
     *
     * @return int
     */
    private function getErrorType($responseBody)
    {
        if (\is_array($responseBody['details']) && isset($responseBody['details'][0]['issue'])) {
            $errorTypeString = $responseBody['details'][0]['issue'];

            if ($errorTypeString === 'INSTRUMENT_DECLINED') {
                $errorType = ErrorCodes::INSTRUMENT_DECLINED;
            } elseif ($errorTypeString === 'TRANSACTION_REFUSED') {
                $errorType = ErrorCodes::TRANSACTION_REFUSED;
            } else {
                $errorType = ErrorCodes::COMMUNICATION_FAILURE;
            }
        } else {
            $errorType = ErrorCodes::UNKNOWN;
        }

        return $errorType;
    }
}
