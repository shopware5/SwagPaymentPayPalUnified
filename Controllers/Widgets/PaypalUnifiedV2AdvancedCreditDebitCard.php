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
            $payPalOrder = $this->orderResource->create($payPalOrderData, $orderParams->getPaymentType(), PartnerAttributionId::PAYPAL_ALL_V2, false);
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
        $paypalOrderId = $this->request->getParam('paypalOrderId');

        if (!$paypalOrderId) {
            throw new UnexpectedValueException('No orderId specified.');
        }

        try {
            $paypalOrder = $this->orderResource->capture($paypalOrderId, PartnerAttributionId::PAYPAL_ALL_V2);
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

        $this->dependencyProvider->getSession()->offsetSet('paypalOrderId', $paypalOrder->getId());
    }

    /**
     * @return void
     */
    public function errorAction()
    {
        $paypalUnifiedErrorCode = $this->request->getParam('code');

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
