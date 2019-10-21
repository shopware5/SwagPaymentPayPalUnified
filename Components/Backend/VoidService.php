<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Backend;

use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\AuthorizationResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\OrderResource;

class VoidService
{
    /**
     * @var ExceptionHandlerServiceInterface
     */
    private $exceptionHandler;

    /**
     * @var AuthorizationResource
     */
    private $authorizationResource;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var PaymentStatusService
     */
    private $paymentStatusService;

    public function __construct(
        ExceptionHandlerServiceInterface $exceptionHandler,
        AuthorizationResource $authorizationResource,
        OrderResource $orderResource,
        PaymentStatusService $paymentStatusService
    ) {
        $this->exceptionHandler = $exceptionHandler;
        $this->authorizationResource = $authorizationResource;
        $this->orderResource = $orderResource;
        $this->paymentStatusService = $paymentStatusService;
    }

    /**
     * @param string $authorizationId
     *
     * @return array
     */
    public function voidAuthorization($authorizationId)
    {
        try {
            $voidData = $this->authorizationResource->void($authorizationId);
            if (strtolower($voidData['state']) === PaymentStatus::PAYMENT_VOIDED) {
                $this->paymentStatusService->updatePaymentStatus(
                    $voidData['parent_payment'],
                    PaymentStatus::PAYMENT_STATUS_CANCELLED
                );
            }
            $viewParameter = [
                'void' => $voidData,
                'success' => true,
            ];
        } catch (\Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'void authorization');

            $viewParameter = [
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ];
        }

        return $viewParameter;
    }

    /**
     * @param string $orderId
     *
     * @return array
     */
    public function voidOrder($orderId)
    {
        try {
            $voidData = $this->orderResource->void($orderId);
            if (strtolower($voidData['state']) === PaymentStatus::PAYMENT_VOIDED) {
                $this->paymentStatusService->updatePaymentStatus(
                    $voidData['parent_payment'],
                    PaymentStatus::PAYMENT_STATUS_CANCELLED
                );
            }
            $viewParameter = [
                'void' => $voidData,
                'success' => true,
            ];
        } catch (\Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'void order');

            $viewParameter = [
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ];
        }

        return $viewParameter;
    }
}
