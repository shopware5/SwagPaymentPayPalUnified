<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Refund;
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

    public function preDispatch()
    {
        parent::preDispatch();

        $this->orderResource = $this->container->get('paypal_unified.v2.order_resource');
        $this->authorizationResource = $this->container->get('paypal_unified.v2.authorization_resource');
        $this->captureResource = $this->container->get('paypal_unified.v2.capture_resource');
        $this->exceptionHandler = $this->container->get('paypal_unified.exception_handler_service');

        $this->container->get('paypal_unified.backend.shop_registration_service')->registerShopById((int) $this->request->getParam('shopId'));
    }

    public function orderDetailsAction()
    {
        $orderId = $this->request->getParam('id');
        if ($orderId === null) {
            $this->view->assign(['success' => false, 'message' => 'There was no orderId provided']);

            return;
        }

        try {
            $paypalOrder = $this->orderResource->get($orderId);
        } catch (Exception $exception) {
            $error = $this->exceptionHandler->handle($exception, 'backend/PaypalUnifiedV2/orderDetails');
            $this->view->assign(['success' => false, 'message' => $error->getCompleteMessage()]);

            return;
        }

        $this->view->assign(['success' => true, 'data' => $paypalOrder]);
    }

    public function captureOrderAction()
    {
        $authorizationId = $this->Request()->getParam('authorizationId');
        $amountToCapture = $this->Request()->getParam('amount');
        $currency = $this->Request()->getParam('currency');
        $finalize = (bool) $this->Request()->getParam('finalize');

        $amount = new Amount();
        $amount->setCurrencyCode($currency);
        $amount->setValue($amountToCapture);

        $capture = new Capture();
        $capture->setAmount($amount);
        $capture->setFinalCapture($finalize === false ? null : true);

        try {
            $this->authorizationResource->capture($authorizationId, $capture, PartnerAttributionId::PAYPAL_ALL_V2);
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
        $captureId = $this->request->getParam('captureId');
        $amountToRefund = $this->request->getParam('amount');
        $currency = $this->Request()->getParam('currency');
        $note = $this->request->getParam('note');

        $amount = new Amount();
        $amount->setCurrencyCode($currency);
        $amount->setValue($amountToRefund);

        $refund = new Refund();
        $refund->setAmount($amount);
        $refund->setNoteToPayer($note);

        try {
            $this->captureResource->refund($captureId, $refund, PartnerAttributionId::PAYPAL_ALL_V2);
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

    public function cancelAuthorizationAction()
    {
        $authorizationId = $this->Request()->getParam('authorizationId');

        try {
            $this->authorizationResource->void($authorizationId, PartnerAttributionId::PAYPAL_ALL_V2);
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
}
