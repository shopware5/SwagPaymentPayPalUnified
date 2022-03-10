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

class Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice extends AbstractPaypalPaymentController
{
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

        $paypalOrderId = $payPalOrder->getId();

        $shopwareOrderNumber = $this->createShopwareOrder($paypalOrderId, PaymentType::PAYPAL_PAY_UPON_INVOICE_V2);
        $paymentStatusService = $this->get('paypal_unified.payment_status_service');
        $paymentStatusService->updatePaymentStatus($paypalOrderId, Status::PAYMENT_STATE_RESERVED);

        if ($this->getSendOrdernumber()) {
            $invoiceIdPatch = $this->createInvoiceIdPatch($shopwareOrderNumber);

            if (!$this->updatePayPalOrder($paypalOrderId, [$invoiceIdPatch])) {
                return;
            }
        }

        if ($this->isPaymentCompleted($paypalOrderId)) {
            $paymentStatusService->updatePaymentStatus($paypalOrderId, Status::PAYMENT_STATE_COMPLETELY_PAID);

            $this->logger->debug(sprintf('%s REDIRECT TO checkout/finish', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $paypalOrderId,
            ]);

            return;
        }

        $this->logger->debug(sprintf('%s SET PAYMENT STATE TO: PAYMENT_STATE_REVIEW_NECESSARY::21', __METHOD__));

        $paymentStatusService->updatePaymentStatus($paypalOrderId, Status::PAYMENT_STATE_REVIEW_NECESSARY);

        $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
            ->setCode(ErrorCodes::COMMUNICATION_FAILURE);

        $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);
    }
}
