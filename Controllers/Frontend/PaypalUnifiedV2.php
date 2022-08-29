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
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common\Link;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;

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

            $this->logger->debug(sprintf('%s %s', __METHOD__, 'IS XHR REQUEST'));
        }
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $session = $this->dependencyProvider->getSession();
        $shopwareSessionOrderData = $session->get('sOrderVariables');
        $this->handleComment();

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

        $payPalOrder = $this->createPayPalOrder($orderParams);
        if (!$payPalOrder instanceof Order) {
            return;
        }

        if ($this->Request()->isXmlHttpRequest()) {
            $this->logger->debug(sprintf('%s IS XHR REQUEST', __METHOD__));

            $this->view->assign('paypalOrderId', $payPalOrder->getId());
            $this->view->assign('basketId', $orderParams->getBasketUniqueId());

            return;
        }

        $url = null;
        foreach ($payPalOrder->getLinks() as $link) {
            if ($link->getRel() === Link::RELATION_APPROVE) {
                $url = $link->getHref();
            }
        }

        if ($url === null) {
            $this->logger->debug(sprintf('%s NO URL FOUND', __METHOD__));

            throw new \RuntimeException('No link for redirect found');
        }

        $this->logger->debug(sprintf('%s REDIRECT TO: %s', __METHOD__, $url));

        $this->redirect($url);
    }

    /**
     * @return void
     */
    public function returnAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $useInContext = (bool) $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_USE_IN_CONTEXT);
        if (!$useInContext) {
            // The "token" is the same as the "paypalOrderId". In this case the request comes directly form the
            // PayPalCheckout page, so we cant influence the wording.
            $payPalOrderId = $this->Request()->getParam('token');
        } else {
            $payPalOrderId = $this->Request()->getParam('paypalOrderId');
        }

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

        $result = null;
        $shopwareOrderNumber = null;
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

        if (!$this->checkCaptureAuthorizationStatus($capturedPayPalOrder)) {
            return;
        }

        if (!$sendShopwareOrderNumber) {
            $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, $this->getPaymentType($payPalOrder));
        }

        $this->setTransactionId($shopwareOrderNumber, $capturedPayPalOrder);

        if ($payPalOrder->getIntent() === PaymentIntentV2::CAPTURE) {
            $this->paymentStatusService->updatePaymentStatus($payPalOrderId, Status::PAYMENT_STATE_COMPLETELY_PAID);
        } else {
            $this->paymentStatusService->updatePaymentStatus($payPalOrderId, Status::PAYMENT_STATE_RESERVED);
        }

        if ($this->Request()->isXmlHttpRequest()) {
            $this->view->assign('paypalOrderId', $payPalOrderId);

            $this->logger->debug(sprintf('%s IS XHR REQUEST', __METHOD__));

            return;
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
