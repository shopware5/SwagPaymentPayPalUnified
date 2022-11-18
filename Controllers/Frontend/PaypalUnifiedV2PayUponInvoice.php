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
        $shopwareSessionOrderData = $session->offsetGet('sOrderVariables');

        $shopwareOrderData = new ShopwareOrderData($shopwareSessionOrderData['sUserData'], $shopwareSessionOrderData['sBasket']);

        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2, $shopwareOrderData);

        $payPalOrder = $this->createPayPalOrder($orderParams);
        if (!$payPalOrder instanceof Order) {
            return;
        }

        $payPalOrderId = $payPalOrder->getId();

        $shopwareOrderNumber = $this->orderNumberService->getOrderNumber();
        $invoiceIdPatch = $this->createInvoiceIdPatch($shopwareOrderNumber);
        if (!$this->updatePayPalOrder($payPalOrderId, [$invoiceIdPatch])) {
            $this->orderNumberService->restoreOrdernumberToPool();

            return;
        }

        if ($this->isPaymentCompleted($payPalOrderId)) {
            $payPalOrder = $this->getPayPalOrder($payPalOrderId);
            if (!$payPalOrder instanceof Order) {
                $this->orderNumberService->restoreOrdernumberToPool();

                return;
            }

            $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, PaymentType::PAYPAL_PAY_UPON_INVOICE_V2, Status::PAYMENT_STATE_COMPLETELY_PAID);

            $this->paymentInstructionService->createInstructions($shopwareOrderNumber, $payPalOrder);

            $this->setTransactionId($shopwareOrderNumber, $payPalOrder);

            $this->logger->debug(sprintf('%s REDIRECT TO checkout/finish', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $payPalOrderId,
            ]);

            return;
        }

        $this->orderNumberService->restoreOrdernumberToPool();

        $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
            ->setCode(ErrorCodes::COMMUNICATION_FAILURE);

        $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);
    }
}
