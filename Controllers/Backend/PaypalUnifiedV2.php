<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
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
        $currency = $this->Request()->getParam('currency');
        $finalize = (bool) $this->Request()->getParam('finalize', false);

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

        if ($refundResult->getStatus() === PaymentStatusV2::ORDER_CAPTURE_COMPLETED) {
            $this->paymentStatusService->updatePaymentStatusV2($shopwareOrderId, Status::PAYMENT_STATE_RE_CREDITING);
        }

        $this->view->assign(['success' => true]);
    }

    public function cancelAuthorizationAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $authorizationId = $this->Request()->getParam('authorizationId');

        try {
            $this->logger->debug(sprintf('%s CANCEL AUTHORIZATION OF PAYPAL ORDER WITH ID: %s', __METHOD__, $authorizationId));

            $this->authorizationResource->void($authorizationId);

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

        $this->view->assign(['success' => true]);
    }
}
