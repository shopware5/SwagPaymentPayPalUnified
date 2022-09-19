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
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderPurchaseUnitPatch;

/**
 * @phpstan-import-type CheckoutBasketArray from \Shopware_Controllers_Frontend_Checkout
 */
class Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout extends AbstractPaypalPaymentController
{
    /**
     * @return void
     */
    public function expressCheckoutFinishAction()
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

        /** @phpstan-var CheckoutBasketArray $basketData */
        $basketData = $this->getBasket() ?: [];
        $userData = $this->getUser() ?: [];

        $shopwareOrderData = new ShopwareOrderData($userData, $basketData);
        $payPalOrderParameter = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2, $shopwareOrderData);

        $payPalOrderData = $this->orderFactory->createOrder($payPalOrderParameter);
        $payPalOrderData->setId($payPalOrderId);

        $purchaseUnitPatch = new OrderPurchaseUnitPatch();
        $purchaseUnitPatch->setPath(OrderPurchaseUnitPatch::PATH);
        $purchaseUnitPatch->setOp(Patch::OPERATION_REPLACE);
        $purchaseUnitPatch->setValue(json_decode((string) json_encode($payPalOrderData->getPurchaseUnits()[0]), true));

        $patchSet = [$purchaseUnitPatch];

        $result = $this->handleOrderWithSendOrderNumber($payPalOrderData, $patchSet);
        if (!$result->getSuccess()) {
            $this->orderNumberService->restoreOrdernumberToPool($result->getShopwareOrderNumber());

            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        if (!$this->updatePayPalOrder($payPalOrderId, $patchSet)) {
            $this->orderNumberService->restoreOrdernumberToPool($result->getShopwareOrderNumber());

            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $payPalOrder = $this->getPayPalOrder($payPalOrderId);
        if (!$payPalOrder instanceof Order) {
            $this->orderNumberService->restoreOrdernumberToPool($result->getShopwareOrderNumber());

            $this->setReviewNecessary($payPalOrderId, $result->getShopwareOrderNumber());

            return;
        }

        $captureAuthorizeResult = $this->captureOrAuthorizeOrder($payPalOrder);
        $capturedPayPalOrder = $captureAuthorizeResult->getOrder();
        if (!$capturedPayPalOrder instanceof Order) {
            if ($captureAuthorizeResult->getRequireRestart()) {
                $this->orderNumberService->releaseOrderNumber();
                $this->restartAction(false, $payPalOrderId, 'frontend', 'PaypalUnifiedV2ExpressCheckout', 'expressCheckoutFinish');

                return;
            }

            $this->orderNumberService->restoreOrdernumberToPool($result->getShopwareOrderNumber());
            $this->setReviewNecessary($payPalOrderId, $result->getShopwareOrderNumber());

            return;
        }

        if (!$this->checkCaptureAuthorizationStatus($capturedPayPalOrder)) {
            $this->orderNumberService->restoreOrdernumberToPool($result->getShopwareOrderNumber());

            return;
        }

        $this->createShopwareOrder($payPalOrderId, PaymentType::PAYPAL_EXPRESS_V2);

        $this->setTransactionId($result->getShopwareOrderNumber(), $capturedPayPalOrder);

        $this->updatePaymentStatus($capturedPayPalOrder->getIntent(), $this->getOrderId($result->getShopwareOrderNumber()));

        $this->logger->debug(sprintf('%s REDIRECT TO checkout/finish', __METHOD__));

        $this->redirect([
            'module' => 'frontend',
            'controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $payPalOrderId,
        ]);
    }

    /**
     * @param string      $payPalOrderId
     * @param string|null $shopwareOrderNumber
     *
     * @return void
     */
    private function setReviewNecessary($payPalOrderId, $shopwareOrderNumber = null)
    {
        if (\is_string($shopwareOrderNumber)) {
            $this->orderDataService->removeTransactionId($shopwareOrderNumber);
            $this->paymentStatusService->updatePaymentStatus($payPalOrderId, Status::PAYMENT_STATE_REVIEW_NECESSARY);
        }
    }
}
