<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;

class Shopware_Controllers_Frontend_PaypalUnifiedV2AdvancedCreditDebitCard extends AbstractPaypalPaymentController
{
    /**
     * @return void
     */
    public function indexAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $session = $this->container->get('session');

        $payPalOrderId = $session->offsetGet('paypalOrderId');
        if (!\is_string($payPalOrderId)) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException(new UnexpectedValueException("Required session parameter 'paypalOrderId' is missing"), '');
            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        if ($this->isPaymentCompleted($payPalOrderId)) {
            $session->offsetUnset('paypalOrderId');

            $payPalOrder = $this->getPayPalOrder($payPalOrderId);
            if (!$payPalOrder instanceof Order) {
                return;
            }

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
        }
    }
}
