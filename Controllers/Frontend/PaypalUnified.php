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

use Shopware\Components\HttpClient\RequestException;
use Shopware\Components\Logger;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\PaymentAddressPatchService;
use SwagPaymentPayPalUnified\Components\Services\PaymentInstructionService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentOrderNumberPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\ErrorResponse;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\RelatedResource;

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
        $userData = $orderData['sUserData'];

        if ($orderData === null) {
            //No order to be processed
            $this->handleError(0);

            return;
        }

        try {
            /** @var PaymentResource $paymentResource */
            $paymentResource = $this->container->get('paypal_unified.payment_resource');

            //Query all information
            $basketData = $orderData['sBasket'];
            $profile = $this->get('paypal_unified.web_profile_service')->getWebProfile();

            $selectedPaymentName = $orderData['sPayment']['name'];

            /** @var Payment $params */
            $params = null;

            //For generic paypal payments like PayPal or PayPal Plus ones,
            //we need a different parameter for the payment creation than in installments
            if ($selectedPaymentName === PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME) {
                $params = $this->get('paypal_unified.payment_request_service')->getRequestParameters(
                    $profile,
                    $basketData,
                    $userData
                );
            } elseif ($selectedPaymentName === PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME) {
                $params = $this->get('paypal_unified.installments_payment_request_service')->getRequestParameters(
                    $profile,
                    $basketData,
                    $userData
                );
            }

            $response = $paymentResource->create($params);

            $responseStruct = Payment::fromArray($response);
        } catch (RequestException $requestEx) {
            //Communication failure
            $this->handleError(2, $requestEx);

            return;
        } catch (\Exception $exception) {
            //Unknown error
            $this->handleError(4);

            return;
        }

        //Patch the address data into the payment.
        //This function is only being called for PayPal classic, therefore,
        //there is an additional action (patchAddressAction()) for the PayPal plus integration.
        /** @var PaymentAddressPatchService $patchService */
        $patchService = $this->get('paypal_unified.payment_address_patch_service');
        $paymentResource->patch($responseStruct->getId(), $patchService->getPatch($userData));

        $this->redirect($responseStruct->getLinks()[1]->getHref());
    }

    public function installmentsReturnAction()
    {
        echo '<pre>';
        print_r(\Doctrine\Common\Util\Debug::dump('yay'));
        echo '</pre>';
        exit();
    }

    /**
     * This action is called when the user is being redirected back from PayPal after a successful payment process. Here we save the order in the system
     * and handle the data exchange with PayPal.
     * Required parameters:
     *  (string) paymentId
     *  (string) PayerID
     */
    public function returnAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $request = $this->Request();
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');

        try {
            $orderNumber = '';

            /** @var OrderDataService $orderDataService */
            $orderDataService = $this->container->get('paypal_unified.order_data_service');

            /** @var PaymentResource $paymentResource */
            $paymentResource = $this->container->get('paypal_unified.payment_resource');
            $sendOrderNumber = (bool) $this->get('paypal_unified.settings_service')->get('send_order_number');

            // if the order number should be send to PayPal do it before the execute
            if ($sendOrderNumber) {
                $orderNumber = $this->saveOrder($paymentId, $paymentId, PaymentStatus::PAYMENT_STATUS_OPEN);
                $patchOrderNumber = $this->container->get('paypal_unified.settings_service')->get('order_number_prefix') . $orderNumber;

                /** @var PaymentOrderNumberPatch $paymentPatch */
                $paymentPatch = new PaymentOrderNumberPatch($patchOrderNumber);

                $paymentResource->patch($paymentId, $paymentPatch);
            }

            // execute the payment to the PayPal API
            $executionResponse = $paymentResource->execute($payerId, $paymentId);
            if ($executionResponse === null) {
                //Communication failure
                $this->handleError(2);

                return;
            }

            // convert the response into a struct
            /** @var Payment $response */
            $response = Payment::fromArray($executionResponse);

            // if the order number is not sent to PayPal do it here to avoid broken orders
            if (!$sendOrderNumber) {
                $orderNumber = $this->saveOrder($paymentId, $paymentId, PaymentStatus::PAYMENT_STATUS_OPEN);
            }

            /** @var RelatedResource $responseSale */
            $responseSale = $response->getTransactions()->getRelatedResources()->getResources()[0];

            // apply the payment status if its completed by PayPal
            $paymentState = $responseSale->getState();
            if ($paymentState === PaymentStatus::PAYMENT_COMPLETED) {
                if (!$orderDataService->applyPaymentStatus($orderNumber, PaymentStatus::PAYMENT_STATUS_APPROVED)) {
                    // Order not found failure
                    $this->handleError(3);

                    return;
                }
            }

            //Use TXN-ID instead of the PaymentId
            $saleId = $responseSale->getId();
            if (!$orderDataService->applyTransactionId($orderNumber, $saleId)) {
                // Order not found failure
                $this->handleError(3);

                return;
            }

            // if we get payment instructions from PayPal save them to database
            if ($response->getPaymentInstruction()) {
                /** @var PaymentInstructionService $instructionService */
                $instructionService = $this->container->get('paypal_unified.payment_instruction_service');
                $instructionService->createInstructions($orderNumber, $response->getPaymentInstruction());
            }

            $orderDataService->applyPaymentTypeAttribute($orderNumber, $response);

            // Done, redirect to the finish page
            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'finish',
            ]);
        } catch (RequestException $exception) {
            //Communication failure
            $this->handleError(2, $exception);
        } catch (\Exception $exception) {
            //Unknown error
            $this->handleError(4);
        }
    }

    /**
     * This action is being called via Ajax by the PayPal-Plus integration only.
     * Required parameters:
     *  (string) paymentId
     */
    public function patchAddressAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $paymentId = $this->Request()->get('paymentId');
        $userData = $this->get('session')->get('sOrderVariables')['sUserData'];

        /** @var PaymentResource $paymentResource */
        $paymentResource = $this->container->get('paypal_unified.payment_resource');

        /** @var PaymentAddressPatchService $patchService */
        $patchService = $this->get('paypal_unified.payment_address_patch_service');

        $paymentResource->patch($paymentId, $patchService->getPatch($userData));
    }

    /**
     * This action will be executed if the user cancels the payment on the PayPal page.
     * It will redirect to the payment selection.
     */
    public function cancelAction()
    {
        $this->handleError(1);
    }

    /**
     * This method handles the redirection to the shippingPayment action if an
     * error has occurred during the payment process.
     *
     * It takes the following parameters from the URL:
     * - code (int): The code of the error that occurred. Depending on the code,
     *               another message will be displayed in the frontend.
     *      0 = No order to be processed
     *      1 = User has canceled the process
     *      2 = Communication failure
     *      3 = System order failure
     *      Any other = Unknown error
     *
     * @param int              $code
     * @param RequestException $exception
     */
    private function handleError($code, RequestException $exception = null)
    {
        if ($exception) {
            //Parse the received error
            $error = ErrorResponse::fromArray(json_decode($exception->getBody(), true));

            if ($error !== null) {
                /** @var Logger $logger */
                $logger = $this->get('pluginlogger');
                $logger->warning('PayPal Unified: Received an error: ' . $error->getMessage(), $error->toArray());
            }
        }

        $this->redirect([
            'controller' => 'checkout',
            'action' => 'shippingPayment',
            'paypal_unified_error_code' => $code,
        ]);
    }
}
