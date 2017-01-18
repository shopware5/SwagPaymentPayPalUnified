<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

use SwagPaymentPayPalUnified\SDK\Resources\PaymentResource;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\SDK\Structs\Payment\Transactions\Sale;
use SwagPaymentPayPalUnified\SDK\Components\Patches\PaymentOrderNumberPatch;
use SwagPaymentPayPalUnified\SDK\Structs\Payment;

class Shopware_Controllers_Frontend_PaypalUnified extends \Shopware_Controllers_Frontend_Payment
{
    /**
     * Index action of the payment. The only thing to do here is to forward to the gateway action.
     */
    public function indexAction()
    {
        $this->forward('gateway');
    }

    /**
     * The gateway to PayPal. The payment will be created and the user will be redirected to the
     * PayPal site.
     */
    public function gatewayAction()
    {
        $orderData = $this->get('session')->get('sOrderVariables');

        /** @var PaymentResource $paymentResource */
        $paymentResource = $this->container->get('paypal_unified.payment_resource');

        $response = $paymentResource->create($orderData);
        /** @var Payment $responseStruct */
        $responseStruct = Payment::fromArray($response);

        $this->redirect($responseStruct->getLinks()->getApprovalUrl());
    }

    /**
     * This action is called when the user is redirected back from PayPal. Here we save the order in the system
     * and handle the data exchange with PayPal
     */
    public function returnAction()
    {
        $request = $this->Request();
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');

        try {
            $orderNumber = '';

            /** @var OrderDataService $orderDataService */
            $orderDataService = $this->container->get('paypal_unified.order_data_service');

            /** @var PaymentResource $paymentResource */
            $paymentResource = $this->container->get('paypal_unified.payment_resource');
            $sendOrderNumber = (bool) $this->get('config')->get('sendOrderNumberToPayPal');

            // if the order number should be send to PayPal do it before the execute
            if ($sendOrderNumber) {
                $orderNumber = $this->saveOrder($paymentId, $paymentId, PaymentStatus::PAYMENT_STATUS_OPEN);
                /** @var PaymentOrderNumberPatch $paymentPatch */
                $paymentPatches[] = new PaymentOrderNumberPatch($orderNumber);

                $paymentResource->patch($paymentId, $paymentPatches);
            }

            // execute the payment to the PayPal API
            $executionResponse = $paymentResource->execute($payerId, $paymentId);

            // convert the response into a struct
            /** @var Payment $response */
            $response = Payment::fromArray($executionResponse);

            // if the order number is not sent to PayPal do it here to avoid broken orders
            if (!$sendOrderNumber) {
                $orderNumber = $this->saveOrder($paymentId, $paymentId, PaymentStatus::PAYMENT_STATUS_OPEN);
            }

            /** @var Sale $responseSale */
            $responseSale = $this->getResponseSale($response);

            // apply the payment status if its completed by PayPal
            $paymentState = $responseSale->getState();
            if ($paymentState === PaymentStatus::PAYMENT_COMPLETED) {
                $orderDataService->applyPaymentStatus($orderNumber, PaymentStatus::PAYMENT_STATUS_APPROVED);
            }

            //Use TXN-ID instead of the PaymentId
            $saleId = $responseSale->getId();
            $orderDataService->applyTransactionId(
                $orderNumber,
                $saleId
            );

            // Done, redirect to the finish page
            $this->redirect(['module' => 'frontend', 'controller' => 'checkout', 'action' => 'finish']);
        } catch (RequestException $exception) {
        }
    }

    /**
     * @param Payment $response
     * @return Sale
     */
    private function getResponseSale(Payment $response)
    {
        return $response->getTransactions()->getRelatedResources()->getSale();
    }
}
