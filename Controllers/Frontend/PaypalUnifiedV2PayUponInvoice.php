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
use SwagPaymentPayPalUnified\Components\Services\PayUponInvoiceInstructionService;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;

class Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice extends AbstractPaypalPaymentController
{
    /**
     * @var PayUponInvoiceInstructionService
     */
    private $paymentInstructionService;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->paymentInstructionService = $this->get('paypal_unified.pay_upon_invoice_instruction_service');
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $session = $this->dependencyProvider->getSession();
        $shopwareSessionOrderData = $session->get('sOrderVariables');

        $shopwareOrderData = new ShopwareOrderData($shopwareSessionOrderData['sUserData'], $shopwareSessionOrderData['sBasket']);

        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2, $shopwareOrderData);

        $payPalOrder = $this->createPayPalOrder($orderParams);
        if (!$payPalOrder instanceof Order) {
            return;
        }

        $payPalOrderId = $payPalOrder->getId();

        // Save basket before create the order
        $basketData = $this->cartRestoreService->getCartData();

        $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, PaymentType::PAYPAL_PAY_UPON_INVOICE_V2, Status::PAYMENT_STATE_RESERVED);

        if ($this->getSendOrdernumber()) {
            $invoiceIdPatch = $this->createInvoiceIdPatch($shopwareOrderNumber);
            if (!$this->updatePayPalOrder($payPalOrderId, [$invoiceIdPatch])) {
                // If an error occurred while updating the PayPalOrder
                // - Set the order and payment state to the order
                $this->paymentStatusService->setOrderAndPaymentStatusForFailedOrder($shopwareOrderNumber);
                // - Restore the basket
                $this->cartRestoreService->restoreCart($basketData);

                return;
            }
        }

        if ($this->isPaymentCompleted($payPalOrderId)) {
            $payPalOrder = $this->getPayPalOrder($payPalOrderId);
            if (!$payPalOrder instanceof Order) {
                return;
            }

            $this->paymentInstructionService->createInstructions($shopwareOrderNumber, $payPalOrder);

            $this->setTransactionId($shopwareOrderNumber, $payPalOrder);

            if ($payPalOrder->getIntent() === PaymentIntentV2::CAPTURE) {
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

            return;
        }

        $this->orderDataService->removeTransactionId($shopwareOrderNumber);

        $this->logger->debug(sprintf('%s SET PAYMENT STATE TO: PAYMENT_STATE_REVIEW_NECESSARY::21', __METHOD__));

        $this->paymentStatusService->updatePaymentStatus($payPalOrderId, Status::PAYMENT_STATE_REVIEW_NECESSARY);

        $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
            ->setCode(ErrorCodes::COMMUNICATION_FAILURE);

        $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);
    }
}
