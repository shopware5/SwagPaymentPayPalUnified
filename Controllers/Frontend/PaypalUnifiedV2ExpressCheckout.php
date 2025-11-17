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
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\PendingException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\RequireRestartException;
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
        $this->logger->debug(\sprintf('%s START', __METHOD__));

        $payPalOrderId = $this->request->getParam('token');

        if (!\is_string($payPalOrderId)) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException(new UnexpectedValueException("Required request parameter 'token' (paypalOrderId) is missing"), '');
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

        if (!$this->updatePayPalOrder($payPalOrderId, $patchSet)) {
            $this->orderNumberService->restoreOrdernumberToPool();

            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $payPalOrder = $this->getPayPalOrder($payPalOrderId);
        if (!$payPalOrder instanceof Order) {
            $this->orderNumberService->restoreOrdernumberToPool();

            return;
        }

        try {
            $payPalOrder = $this->captureOrAuthorizeOrder($payPalOrder);
        } catch (RequireRestartException $requireRestartException) {
            $this->logger->debug(\sprintf('%s REQUIRES A RESTART', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'PaypalUnifiedV2ExpressCheckout',
                'action' => 'expressCheckoutFinish',
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

        $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, PaymentType::PAYPAL_EXPRESS_V2);

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
}
