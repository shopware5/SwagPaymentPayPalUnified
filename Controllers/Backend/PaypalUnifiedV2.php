<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\OrderPropertyHelper;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Refund;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\AuthorizationResource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\CaptureResource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;

class Shopware_Controllers_Backend_PaypalUnifiedV2 extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var OrderPropertyHelper
     */
    private $orderPropertyHelper;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var CaptureResource
     */
    private $captureResource;

    /**
     * @var ExceptionHandlerService
     */
    private $exceptionHandler;

    /**
     * @var AuthorizationResource
     */
    private $authorizationResource;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var PaymentStatusService
     */
    private $paymentStatusService;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->orderResource = $this->container->get('paypal_unified.v2.order_resource');
        $this->authorizationResource = $this->container->get('paypal_unified.v2.authorization_resource');
        $this->captureResource = $this->container->get('paypal_unified.v2.capture_resource');
        $this->exceptionHandler = $this->container->get('paypal_unified.exception_handler_service');
        $this->logger = $this->container->get('paypal_unified.logger_service');
        $this->paymentStatusService = $this->container->get('paypal_unified.payment_status_service');
        $this->orderPropertyHelper = $this->get('paypal_unified.order_property_helper');

        $shopId = (int) $this->request->getParam('shopId', 0);
        if ($shopId === 0) {
            throw new UnexpectedValueException('Request parameter shopId is required');
        }

        $this->container->get('paypal_unified.backend.shop_registration_service')->registerShopById($shopId);
    }

    public function orderDetailsAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $orderId = $this->request->getParam('id');
        if ($orderId === null) {
            $this->view->assign(['success' => false, 'message' => 'There was no orderId provided']);

            return;
        }

        try {
            $this->logger->debug(sprintf('%s GET PAYPAL ORDER WITH ID : %s', __METHOD__, $orderId));

            $paypalOrder = $this->orderResource->get($orderId);

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY LOADED', __METHOD__));
        } catch (Exception $exception) {
            $error = $this->exceptionHandler->handle($exception, 'backend/PaypalUnifiedV2/orderDetails');
            $this->view->assign(['success' => false, 'message' => $error->getCompleteMessage()]);

            return;
        }

        $this->view->assign(['success' => true, 'data' => $paypalOrder]);
    }

    public function captureOrderAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $authorizationId = $this->Request()->getParam('authorizationId');
        $amountToCapture = $this->Request()->getParam('amount');
        $maxCaptureAmount = $this->Request()->getParam('maxCaptureAmount');
        $currency = $this->Request()->getParam('currency');
        $finalize = (bool) $this->Request()->getParam('finalize', false);
        $shopwareOrderId = $this->Request()->getParam('shopwareOrderId');

        $amount = new Amount();
        $amount->setCurrencyCode($currency);
        $amount->setValue($amountToCapture);

        $capture = new Capture();
        $capture->setAmount($amount);
        $capture->setFinalCapture($finalize);

        try {
            $this->logger->debug(sprintf('%s CAPTURE PAYPAL ORDER WITH ID: %s', __METHOD__, $authorizationId));

            $this->authorizationResource->capture($authorizationId, $capture);

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY AUTHORIZED', __METHOD__));
        } catch (Exception $exception) {
            $payPalException = $this->exceptionHandler->handle($exception, 'backend/PaypalUnifiedV2/captureOrder');
            $this->view->assign([
                'success' => false,
                'code' => $payPalException->getCode(),
                'message' => $payPalException->getMessage(),
            ]);

            return;
        }

        $this->paymentStatusService->updatePaymentStatusV2(
            $shopwareOrderId,
            $this->paymentStatusService->determinePaymentStausForCapturing($finalize, $amountToCapture, $maxCaptureAmount)
        );

        $this->view->assign(['success' => true]);
    }

    public function refundOrderAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $shopwareOrderId = (int) $this->request->getParam('shopwareOrderId');
        $captureId = $this->request->getParam('captureId');
        $amountToRefund = $this->request->getParam('amount');
        $currency = $this->request->getParam('currency');
        $note = $this->request->getParam('note');
        $maxCaptureAmount = $this->request->getParam('maxCaptureAmount');

        $amount = new Amount();
        $amount->setCurrencyCode($currency);
        $amount->setValue($amountToRefund);

        $refund = new Refund();
        $refund->setAmount($amount);
        $refund->setNoteToPayer($note);

        try {
            $this->logger->debug(sprintf('%s REFUND PAYPAL ORDER WITH ID: %s AMOUNT: %d', __METHOD__, $captureId, $amount->getValue()));

            $refundResult = $this->captureResource->refund($captureId, $refund);

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY REFUNDED', __METHOD__));
        } catch (Exception $exception) {
            $payPalException = $this->exceptionHandler->handle($exception, 'backend/PaypalUnifiedV2/refundOrder');
            $this->view->assign([
                'success' => false,
                'code' => $payPalException->getCode(),
                'message' => $payPalException->getMessage(),
            ]);

            return;
        }

        if ($refundResult->getStatus() === PaymentStatusV2::ORDER_REFUND_COMPLETED) {
            $newStatus = Status::PAYMENT_STATE_RE_CREDITING;
            if ($amountToRefund < $maxCaptureAmount) {
                $newStatus = Status::PAYMENT_STATE_PARTIALLY_PAID;
            }

            $this->paymentStatusService->updatePaymentStatusV2($shopwareOrderId, $newStatus);
        }

        $this->view->assign(['success' => true]);
    }

    public function cancelAuthorizationAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $authorizationId = $this->Request()->getParam('authorizationId');
        $paypalOrderId = $this->Request()->getParam('token');
        $shopwareOrderId = $this->Request()->getParam('shopwareOrderId');

        try {
            $this->logger->debug(sprintf('%s CANCEL AUTHORIZATION OF PAYPAL ORDER WITH ID: %s', __METHOD__, $authorizationId));

            $this->authorizationResource->void($authorizationId);

            $paypalOrder = $this->orderResource->get($paypalOrderId);

            $this->logger->debug(sprintf('%s CANCEL AUTHORIZATION SUCCESSFUL', __METHOD__));
        } catch (Exception $exception) {
            $payPalException = $this->exceptionHandler->handle($exception, 'backend/PaypalUnifiedV2/cancelAuthorization');
            $this->view->assign([
                'success' => false,
                'code' => $payPalException->getCode(),
                'message' => $payPalException->getMessage(),
            ]);

            return;
        }

        $authorization = $this->orderPropertyHelper->getAuthorization($paypalOrder);
        if ($authorization instanceof Authorization && $authorization->getStatus() === PaymentStatusV2::ORDER_VOIDED) {
            $this->paymentStatusService->updatePaymentStatusV2(
                $shopwareOrderId,
                Status::PAYMENT_STATE_THE_PROCESS_HAS_BEEN_CANCELLED
            );
        }

        $this->view->assign(['success' => true]);
    }
}
