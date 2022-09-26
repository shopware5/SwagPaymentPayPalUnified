<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

class Shopware_Controllers_Frontend_PaypalUnifiedV2AdvancedCreditDebitCard extends AbstractPaypalPaymentController
{
    /**
     * @return void
     */
    public function indexAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $session = $this->dependencyProvider->getSession();

        $payPalOrderId = $session->offsetGet('paypalOrderId');
        $shopwareOrderNumber = $session->offsetGet(self::ACDC_SHOPWARE_ORDER_ID_SESSION_KEY);
        $session->offsetUnset(self::ACDC_SHOPWARE_ORDER_ID_SESSION_KEY);

        if (!\is_string($payPalOrderId)) {
            $this->orderNumberService->restoreOrdernumberToPool($shopwareOrderNumber);

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

            $this->createShopwareOrder($payPalOrderId, PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD);

            $this->setTransactionId($shopwareOrderNumber, $payPalOrder);

            $this->updatePaymentStatus($payPalOrder->getIntent(), $this->getOrderId($shopwareOrderNumber));

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
