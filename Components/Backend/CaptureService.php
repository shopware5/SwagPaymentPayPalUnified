<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Backend;

use Exception;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\AuthorizationResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\CaptureResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\OrderResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\CaptureRefund;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\Amount;

class CaptureService
{
    /**
     * @var ExceptionHandlerServiceInterface
     */
    private $exceptionHandler;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var AuthorizationResource
     */
    private $authorizationResource;

    /**
     * @var CaptureResource
     */
    private $captureResource;

    /**
     * @var PaymentStatusService
     */
    private $paymentStatusService;

    public function __construct(
        ExceptionHandlerServiceInterface $exceptionHandler,
        OrderResource $orderResource,
        AuthorizationResource $authorizationResource,
        CaptureResource $captureResource,
        PaymentStatusService $paymentStatusService
    ) {
        $this->exceptionHandler = $exceptionHandler;
        $this->orderResource = $orderResource;
        $this->authorizationResource = $authorizationResource;
        $this->captureResource = $captureResource;
        $this->paymentStatusService = $paymentStatusService;
    }

    /**
     * @param string $orderId
     * @param string $amountToCapture
     * @param string $currency
     * @param bool   $isFinal
     *
     * @return array
     */
    public function captureOrder($orderId, $amountToCapture, $currency, $isFinal)
    {
        $capture = $this->createCapture($amountToCapture, $currency, $isFinal);

        try {
            $captureData = $this->orderResource->capture($orderId, $capture);
            $this->updateCapturePaymentStatus($captureData, $isFinal);

            $viewParameter = ['success' => true];
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'capture order');

            $viewParameter = [
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ];
        }

        return $viewParameter;
    }

    /**
     * @param string $authorizationId
     * @param string $amountToCapture
     * @param string $currency
     * @param bool   $isFinal
     *
     * @return array
     */
    public function captureAuthorization($authorizationId, $amountToCapture, $currency, $isFinal)
    {
        $capture = $this->createCapture($amountToCapture, $currency, $isFinal);

        try {
            $captureData = $this->authorizationResource->capture($authorizationId, $capture);
            $this->updateCapturePaymentStatus($captureData, $isFinal);

            $viewParameter = ['success' => true];
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'capture authorization');

            $viewParameter = [
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ];
        }

        return $viewParameter;
    }

    /**
     * @param string $captureId
     * @param string $totalAmount
     * @param string $currency
     * @param string $description
     *
     * @return array
     */
    public function refundCapture($captureId, $totalAmount, $currency, $description)
    {
        $amountStruct = new Amount();
        $amountStruct->setTotal($totalAmount);
        $amountStruct->setCurrency($currency);

        $refund = new CaptureRefund();
        $refund->setDescription($description);
        $refund->setAmount($amountStruct);

        try {
            $refundData = $this->captureResource->refund($captureId, $refund);

            if ($refundData['state'] === PaymentStatus::PAYMENT_COMPLETED) {
                $this->paymentStatusService->updatePaymentStatus(
                    $refundData['parent_payment'],
                    Status::PAYMENT_STATE_RE_CREDITING
                );
            }

            $viewParameter = [
                'refund' => $refundData,
                'success' => true,
            ];
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'refund capture');

            $viewParameter = [
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ];
        }

        return $viewParameter;
    }

    /**
     * @param string $amountToCapture
     * @param string $currency
     * @param bool   $isFinal
     *
     * @return Capture
     */
    private function createCapture($amountToCapture, $currency, $isFinal)
    {
        $amount = new Amount();
        $amount->setTotal($amountToCapture);
        $amount->setCurrency($currency);

        $capture = new Capture();
        $capture->setAmount($amount);
        $capture->setIsFinalCapture($isFinal);

        return $capture;
    }

    /**
     * @param bool $isFinal
     *
     * @return void
     */
    private function updateCapturePaymentStatus(array $captureData, $isFinal)
    {
        if (\strtolower($captureData['state']) === PaymentStatus::PAYMENT_COMPLETED) {
            if ($isFinal) {
                $this->paymentStatusService->updatePaymentStatus(
                    $captureData['parent_payment'],
                    Status::PAYMENT_STATE_COMPLETELY_PAID
                );
            } else {
                $this->paymentStatusService->updatePaymentStatus(
                    $captureData['parent_payment'],
                    Status::PAYMENT_STATE_PARTIALLY_PAID
                );
            }
        }
    }
}
