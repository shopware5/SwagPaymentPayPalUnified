<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\Exception\InvalidOrderException;
use SwagPaymentPayPalUnified\Components\Exception\TimeoutInfoException;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Components\Services\TimeoutRefundService;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\EmptyCartException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InvalidBillingAddressException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InvalidShippingAddressException;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common\Link;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;

class Shopware_Controllers_Frontend_PaypalUnifiedApm extends AbstractPaypalPaymentController
{
    /**
     * @var TimeoutRefundService
     */
    private $timeoutRefundService;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->timeoutRefundService = $this->get('swag_payment_paypal_unified.timeout_refund_service');
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $requestId = $this->requestIdService->getRequestIdFromRequest($this->Request());
        $isRequestIdAlreadyUsed = $this->requestIdService->checkRequestIdIsAlreadySetToSession($requestId);
        if ($isRequestIdAlreadyUsed) {
            return;
        }

        $this->requestIdService->saveRequestIdToSession($requestId);

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

        try {
            $payPalOrder = $this->createPayPalOrder($orderParams);
        } catch (InvalidBillingAddressException $invalidBillingAddressException) {
            $this->redirectInvalidAddress(['invalidBillingAddress' => true]);

            return;
        } catch (InvalidShippingAddressException $invalidShippingAddressException) {
            $this->redirectInvalidAddress(['invalidShippingAddress' => true]);

            return;
        } catch (EmptyCartException $emptyCartException) {
            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'cart',
            ]);

            return;
        }

        if (!$payPalOrder instanceof Order) {
            return;
        }

        $this->timeoutRefundService->saveInfo($payPalOrder->getId(), $this->getAmount());

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
            if ($this->getUser() === null) {
                try {
                    $this->timeoutRefundService->refund($payPalOrderId, $this->getCaptureId($payPalOrder));
                } catch (TimeoutInfoException $exception) {
                    $this->logger->error($exception->getMessage());
                } catch (InvalidOrderException $exception) {
                    $this->logger->error($exception->getMessage());
                }

                $this->timeoutRefundService->deleteInfo($payPalOrderId);

                $this->redirect([
                    'module' => 'frontend',
                    'controller' => 'register',
                    'action' => 'index',
                    'paymentApproveTimeout' => true,
                ]);

                return;
            }

            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::BASKET_VALIDATION_ERROR);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $this->timeoutRefundService->deleteInfo($payPalOrderId);

        if ($this->Request()->isXmlHttpRequest()) {
            $this->view->assign('token', $payPalOrderId);

            $this->logger->debug(sprintf('%s IS XHR REQUEST', __METHOD__));

            return;
        }

        if ($this->isPaymentCompleted($payPalOrderId)) {
            $payPalOrder = $this->getPayPalOrder($payPalOrderId);
            if (!$payPalOrder instanceof Order) {
                return;
            }

            if ($this->checkIfTransactionIdIsAlreadyAssigned($payPalOrder)) {
                $this->orderNumberService->restoreOrdernumberToPool();

                return;
            }

            $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, $this->getPaymentType());

            $this->setTransactionId($shopwareOrderNumber, $payPalOrder);

            $this->updatePaymentStatus($payPalOrder->getIntent(), $this->getOrderId($shopwareOrderNumber));

            $this->logger->debug(sprintf('%s REDIRECT TO checkout/finish', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $payPalOrderId,
            ]);

            return;
        }

        $paymentType = $this->getPaymentType();

        $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, $paymentType);

        $this->logger->warning(
            sprintf('A payment with type: %s has failed. Review previous error messages to clarify the issue. The customer was invited to contact the merchant.', $paymentType),
            [
                'paymentType' => $paymentType,
                'paypalOrderId' => $payPalOrderId,
                'shopwareOrderNumber' => $shopwareOrderNumber,
            ]
        );

        $this->redirect([
            'module' => 'frontend',
            'controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $payPalOrderId,
            'requireContactToMerchant' => true,
        ]);
    }

    /**
     * @return string
     */
    private function getCaptureId(Order $payPalOrder)
    {
        $capture = $this->orderPropertyHelper->getFirstCapture($payPalOrder);
        if (!$capture instanceof Capture) {
            throw new InvalidOrderException($payPalOrder->getId());
        }

        return $capture->getId();
    }
}
