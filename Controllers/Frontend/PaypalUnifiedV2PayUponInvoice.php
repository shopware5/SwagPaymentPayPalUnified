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
        $session = $this->dependencyProvider->getSession();
        $shopwareSessionOrderData = $session->get('sOrderVariables');

        $shopwareOrderData = new ShopwareOrderData($shopwareSessionOrderData['sUserData'], $shopwareSessionOrderData['sBasket']);

        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2, $shopwareOrderData);

        try {
            $orderData = $this->orderFactory->createOrder($orderParams);

            $paypalOrder = $this->orderResource->create($orderData, $orderParams->getPaymentType(), PartnerAttributionId::PAYPAL_ALL_V2, false);
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

        $shopwareOrderNumber = $this->createShopwareOrder($paypalOrder->getId(), PaymentType::PAYPAL_PAY_UPON_INVOICE_V2);

        if ($this->settingsService->get(SettingsServiceInterface::SETTING_SEND_ORDER_NUMBER)) {
            $orderNumberPrefix = $this->settingsService->get(SettingsServiceInterface::SETTING_ORDER_NUMBER_PREFIX);

            $invoiceIdPatch = new OrderAddInvoiceIdPatch();
            $invoiceIdPatch->setOp(Patch::OPERATION_ADD);
            $invoiceIdPatch->setValue(sprintf('%s%s', $orderNumberPrefix, $shopwareOrderNumber));
            $invoiceIdPatch->setPath(OrderAddInvoiceIdPatch::PATH);

            $patchSet[] = $invoiceIdPatch;

            try {
                $this->orderResource->update($patchSet, $paypalOrder->getId(), PartnerAttributionId::PAYPAL_ALL_V2);
            } catch (RequestException $exception) {
                $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                    ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                    ->setException($exception);

                $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

                return;
            }
        }

        /** @var PaymentStatusService $paymentStatusService */
        $paymentStatusService = $this->get('paypal_unified.payment_status_service');

        // TODO: (PT-12529) Implement webhook solution
        // TODO: (PT-12529) Send response, implement a "waiting" template using a widget controller (which reacts to the webhook call OR does the polling) + AJAX for local polling
        if ($this->isPaymentCompleted($paypalOrder->getId())) {
            $paymentStatusService->updatePaymentStatus($paypalOrder->getId(), Status::PAYMENT_STATE_COMPLETELY_INVOICED);

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $paypalOrder->getId(),
            ]);
        } else {
            $paymentStatusService->updatePaymentStatus($paypalOrder->getId(), Status::PAYMENT_STATE_REVIEW_NECESSARY);

            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);
        }
    }
}
