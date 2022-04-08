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
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;

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

        $purchaseUnitPatch = new OrderPurchaseUnitPatch();
        $purchaseUnitPatch->setPath(OrderPurchaseUnitPatch::PATH);
        $purchaseUnitPatch->setOp(Patch::OPERATION_REPLACE);
        $purchaseUnitPatch->setValue(json_decode((string) json_encode($payPalOrderData->getPurchaseUnits()[0]), true));

        $patchSet = [$purchaseUnitPatch];

        $shopwareOrderNumber = null;
        $sendShopwareOrderNumber = $this->getSendOrdernumber();
        if ($sendShopwareOrderNumber) {
            $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, PaymentType::PAYPAL_EXPRESS_V2);

            $patchSet[] = $this->createInvoiceIdPatch($shopwareOrderNumber);
        }

        if (!$this->updatePayPalOrder($payPalOrderId, $patchSet)) {
            return;
        }

        $capturedPayPalOrder = $this->captureOrAuthorizeOrder($payPalOrderId);
        if (!$capturedPayPalOrder instanceof Order) {
            if (\is_string($shopwareOrderNumber)) {
                $this->orderDataService->removeTransactionId($shopwareOrderNumber);
                $this->paymentStatusService->updatePaymentStatus($payPalOrderId, Status::PAYMENT_STATE_REVIEW_NECESSARY);
            }

            return;
        }

        if (!$sendShopwareOrderNumber) {
            $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, PaymentType::PAYPAL_EXPRESS_V2);
        }

        $this->setTransactionId($shopwareOrderNumber, $capturedPayPalOrder);

        if ($capturedPayPalOrder->getIntent() === PaymentIntentV2::CAPTURE) {
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
