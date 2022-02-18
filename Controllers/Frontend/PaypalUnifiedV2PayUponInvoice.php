<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\HttpClient\RequestException;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderAddInvoiceIdPatch;

class Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice extends AbstractPaypalPaymentController
{
    const MAXIMUM_RETRIES = 32;
    const TIMEOUT = \CURLOPT_TIMEOUT * 2;
    const SLEEP = 1;

    public function indexAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $session = $this->dependencyProvider->getSession();
        $shopwareSessionOrderData = $session->get('sOrderVariables');

        $shopwareOrderData = new ShopwareOrderData($shopwareSessionOrderData['sUserData'], $shopwareSessionOrderData['sBasket']);

        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2, $shopwareOrderData);

        try {
            $this->logger->debug(sprintf('%s BEFORE CREATE PAYPAL ORDER', __METHOD__));

            $orderData = $this->orderFactory->createOrder($orderParams);

            $paypalOrder = $this->orderResource->create($orderData, $orderParams->getPaymentType(), PartnerAttributionId::PAYPAL_ALL_V2, false);

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFUL CREATED: ID: %d', __METHOD__, $paypalOrder->getId()));
        } catch (RequestException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        } catch (\Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        /** @var PaymentStatusService $paymentStatusService */
        $paymentStatusService = $this->get('paypal_unified.payment_status_service');

        $shopwareOrderNumber = $this->createShopwareOrder($paypalOrder->getId(), PaymentType::PAYPAL_PAY_UPON_INVOICE_V2);
        $paymentStatusService->updatePaymentStatus($paypalOrder->getId(), Status::PAYMENT_STATE_RESERVED);

        if ($this->settingsService->get(SettingsServiceInterface::SETTING_SEND_ORDER_NUMBER)) {
            $orderNumberPrefix = $this->settingsService->get(SettingsServiceInterface::SETTING_ORDER_NUMBER_PREFIX);

            $invoiceIdPatch = new OrderAddInvoiceIdPatch();
            $invoiceIdPatch->setOp(Patch::OPERATION_ADD);
            $invoiceIdPatch->setValue(sprintf('%s%s', $orderNumberPrefix, $shopwareOrderNumber));
            $invoiceIdPatch->setPath(OrderAddInvoiceIdPatch::PATH);

            $patchSet[] = $invoiceIdPatch;

            try {
                $this->logger->debug(sprintf('%s UPDATE PAYPAL ORDER WITH ID: %s', __METHOD__, $paypalOrder->getId()));

                $this->orderResource->update($patchSet, $paypalOrder->getId(), PartnerAttributionId::PAYPAL_ALL_V2);

                $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY UPDATED', __METHOD__));
            } catch (RequestException $exception) {
                $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                    ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                    ->setException($exception);

                $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

                return;
            }
        }

        // TODO: (PT-12529) Implement webhook solution
        // TODO: (PT-12529) Send response, implement a "waiting" template using a widget controller (which reacts to the webhook call OR does the polling) + AJAX for local polling
        if ($this->isPaymentCompleted($paypalOrder->getId())) {
            $paymentStatusService->updatePaymentStatus($paypalOrder->getId(), Status::PAYMENT_STATE_COMPLETELY_PAID);

            $this->logger->debug(sprintf('%s REDIRECT TO checkout/finish', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $paypalOrder->getId(),
            ]);
        } else {
            $this->logger->debug(sprintf('%s SET PAYMENT STATE TO: PAYMENT_STATE_REVIEW_NECESSARY::21', __METHOD__));

            $paymentStatusService->updatePaymentStatus($paypalOrder->getId(), Status::PAYMENT_STATE_REVIEW_NECESSARY);

            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);
        }
    }
}
