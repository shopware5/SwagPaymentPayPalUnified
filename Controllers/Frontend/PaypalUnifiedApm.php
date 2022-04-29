<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common\Link;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;

class Shopware_Controllers_Frontend_PaypalUnifiedApm extends AbstractPaypalPaymentController
{
    /**
     * @return void
     */
    public function indexAction()
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

        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(
            $this->getPaymentTypeByName($shopwareOrderData->getShopwareUserData()['additional']['payment']['name']),
            $shopwareOrderData
        );

        $payPalOrder = $this->createPayPalOrder($orderParams);
        if (!$payPalOrder instanceof Order) {
            return;
        }

        $url = $this->getUrl($payPalOrder, Link::RELATION_PAYER_ACTION_REQUIRED);

        $this->logger->debug(sprintf('%s REDIRECT TO: %s', __METHOD__, $url));

        $this->redirect($url);
    }

    /**
     * This action is called when the user is being redirected back from PayPal after a successful payment process.
     * The order is saved here in the system and handle the data exchange with PayPal.
     * Required parameters:
     *  (string) paymentId
     *  (string) PayerID
     *
     * @return void
     */
    public function returnAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $payPalOrderId = $this->Request()->getParam('token');

        $payPalOrder = $this->getPayPalOrder($payPalOrderId);
        if (!$payPalOrder instanceof Order) {
            return;
        }

        if (!$this->isCartValid($payPalOrder)) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::BASKET_VALIDATION_ERROR);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $shopwareOrderNumber = null;
        $result = null;
        $sendShopwareOrderNumber = $this->getSendOrdernumber();

        if ($sendShopwareOrderNumber) {
            $result = $this->handleOrderWithSendOrderNumber($payPalOrder, $this->getPaymentType($payPalOrder));
            $shopwareOrderNumber = $result->getShopwareOrderNumber();
            if (!$result->getSuccess()) {
                $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                    ->setCode(ErrorCodes::COMMUNICATION_FAILURE);

                $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

                return;
            }
        }

        $capturedPayPalOrder = $this->captureOrAuthorizeOrder($payPalOrder->getId(), $payPalOrder, $result);
        if (!$capturedPayPalOrder instanceof Order) {
            if (\is_string($shopwareOrderNumber)) {
                $this->orderDataService->removeTransactionId($shopwareOrderNumber);
                $this->paymentStatusService->updatePaymentStatus($payPalOrderId, Status::PAYMENT_STATE_REVIEW_NECESSARY);
            }

            return;
        }

        if (!$sendShopwareOrderNumber) {
            $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, $this->getPaymentType($payPalOrder));
        }

        if ($this->Request()->isXmlHttpRequest()) {
            $this->view->assign('paypalOrderId', $payPalOrderId);

            $this->logger->debug(sprintf('%s IS XHR REQUEST', __METHOD__));

            return;
        }

        if ($this->isPaymentCompleted($payPalOrderId)) {
            $payPalOrder = $this->getPayPalOrder($payPalOrderId);
            if (!$payPalOrder instanceof Order) {
                return;
            }
            $this->setTransactionId($shopwareOrderNumber, $payPalOrder);

            if ($payPalOrder->getIntent() === PaymentIntentV2::CAPTURE) {
                $this->paymentStatusService->updatePaymentStatus($payPalOrderId, Status::PAYMENT_STATE_COMPLETELY_PAID);
            } else {
                $this->paymentStatusService->updatePaymentStatus($payPalOrderId, Status::PAYMENT_STATE_RESERVED);
            }

            $this->logger->debug(sprintf('%s REDIRECT TO checkout/finish', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $payPalOrderId,
            ]);
        }
    }
}
