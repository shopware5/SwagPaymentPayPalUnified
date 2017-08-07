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
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\PaymentInstructionService;
use SwagPaymentPayPalUnified\Components\Services\ShippingAddressRequestService;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketIdWhitelist;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketValidatorInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAddressPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentOrderNumberPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\ErrorResponse;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\GenericErrorResponse;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\RelatedResource;

class Shopware_Controllers_Frontend_PaypalUnified extends \Shopware_Controllers_Frontend_Payment
{
    /**
     * @var PaymentResource
     */
    private $paymentResource;

    /**
     * @var ClientService
     */
    private $client;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * initialize payment resource
     */
    public function preDispatch()
    {
        $this->paymentResource = $this->container->get('paypal_unified.payment_resource');
        $this->client = $this->container->get('paypal_unified.client_service');
        $this->logger = $this->container->get('paypal_unified.logger_service');
    }

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
            $this->handleError(ErrorCodes::NO_ORDER_TO_PROCESS);

            return;
        }

        try {
            //Query all information
            $basketData = $orderData['sBasket'];
            $webProfileId = $this->get('paypal_unified.settings_service')->get('web_profile_id');

            $selectedPaymentName = $orderData['sPayment']['name'];

            $requestParams = new PaymentBuilderParameters();
            $requestParams->setBasketData($basketData);
            $requestParams->setUserData($userData);
            $requestParams->setWebProfileId($webProfileId);

            //Prepare the new basket signature feature, announced in SW 5.3.0
            if (version_compare($this->container->get('config')->offsetGet('version'), '5.3.0', '>=')) {
                $basketUniqueId = $this->persistBasket();
                $requestParams->setBasketUniqueId($basketUniqueId);
            }

            /** @var Payment $payment */
            $payment = null;

            //For generic paypal payments like PayPal or PayPal Plus ones,
            //we need a different parameter for the payment creation than in installments
            if ($selectedPaymentName === PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME) {
                $requestParams->setPaymentType(PaymentType::PAYPAL_CLASSIC);
                $payment = $this->get('paypal_unified.payment_builder_service')->getPayment($requestParams);
            } elseif ($selectedPaymentName === PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME) {
                $this->client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_INSTALLMENTS);
                $requestParams->setPaymentType(PaymentType::PAYPAL_INSTALLMENTS);
                $payment = $this->get('paypal_unified.installments.payment_builder_service')->getPayment($requestParams);
            }

            $response = $this->paymentResource->create($payment);

            $responseStruct = Payment::fromArray($response);
        } catch (RequestException $requestEx) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $requestEx);

            return;
        } catch (\Exception $exception) {
            $this->handleError(ErrorCodes::UNKNOWN);

            return;
        }

        //Patch the address data into the payment.
        //This function is only being called for PayPal classic, therefore,
        //there is an additional action (patchAddressAction()) for the PayPal plus integration.
        /** @var ShippingAddressRequestService $addressService */
        $addressService = $this->get('paypal_unified.shipping_address_request_service');
        $addressPatch = new PaymentAddressPatch($addressService->getAddress($userData));

        $this->paymentResource->patch($responseStruct->getId(), $addressPatch);

        if ($this->Request()->getParam('useInContext')) {
            $this->Front()->Plugins()->Json()->setRenderer();

            $this->View()->assign('paymentId', $responseStruct->getId());

            return;
        }

        $this->redirect($responseStruct->getLinks()[1]->getHref());
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
        $paymentId = $request->getParam('paymentId');
        $payerId = $request->getParam('PayerID');
        $basketId = $request->getParam('basketId');

        try {
            $orderNumber = '';

            /** @var OrderDataService $orderDataService */
            $orderDataService = $this->get('paypal_unified.order_data_service');

            $sendOrderNumber = (bool) $this->get('paypal_unified.settings_service')->get('send_order_number');

            // if the order number should be send to PayPal do it before the execute
            if ($sendOrderNumber) {
                $orderNumber = $this->saveOrder($paymentId, $paymentId, PaymentStatus::PAYMENT_STATUS_OPEN);
                $patchOrderNumber = $this->container->get('paypal_unified.settings_service')->get('order_number_prefix') . $orderNumber;

                /** @var PaymentOrderNumberPatch $paymentPatch */
                $paymentPatch = new PaymentOrderNumberPatch($patchOrderNumber);

                $this->paymentResource->patch($paymentId, $paymentPatch);
            }

            //Basket validation with shopware 5.2 support
            if (in_array($basketId, BasketIdWhitelist::WHITELIST_IDS) || version_compare($this->container->get('config')->get('version'), '5.3.0', '<')) {
                //For shopware < 5.3 and for whitelisted basket ids
                $payment = $this->paymentResource->get($paymentId);
                $basketValid = $this->validateBasketSimple(Payment::fromArray($payment));
            } else {
                //For shopware > 5.3
                $basketValid = $this->validateBasketExtended($basketId);
            }

            if (!$basketValid) {
                $this->handleError(ErrorCodes::BASKET_VALIDATION_ERROR);

                return;
            }

            // execute the payment to the PayPal API
            $executionResponse = $this->paymentResource->execute($payerId, $paymentId);
            if ($executionResponse === null) {
                $this->handleError(ErrorCodes::COMMUNICATION_FAILURE);

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
                    $this->handleError(ErrorCodes::NO_ORDER_TO_PROCESS);

                    return;
                }
            }

            //Use TXN-ID instead of the PaymentId
            $saleId = $responseSale->getId();
            if (!$orderDataService->applyTransactionId($orderNumber, $saleId)) {
                $this->handleError(ErrorCodes::NO_ORDER_TO_PROCESS);

                return;
            }

            // if we get payment instructions from PayPal save them to database
            if ($response->getPaymentInstruction()) {
                /** @var PaymentInstructionService $instructionService */
                $instructionService = $this->get('paypal_unified.payment_instruction_service');
                $instructionService->createInstructions($orderNumber, $response->getPaymentInstruction());
            }

            $isExpressCheckout = (bool) $request->getParam('expressCheckout', false);
            $orderDataService->applyPaymentTypeAttribute($orderNumber, $response, $isExpressCheckout);

            // Done, redirect to the finish page
            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'finish',
            ]);
        } catch (RequestException $exception) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $exception);
        } catch (\Exception $exception) {
            $this->handleError(ErrorCodes::UNKNOWN);
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

        $paymentId = $this->Request()->getParam('paymentId');
        $userData = $this->get('session')->get('sOrderVariables')['sUserData'];

        /** @var ShippingAddressRequestService $patchService */
        $addressService = $this->get('paypal_unified.shipping_address_request_service');
        $addressPatch = new PaymentAddressPatch($addressService->getAddress($userData));

        $this->paymentResource->patch($paymentId, $addressPatch);
    }

    /**
     * This action will be executed if the user cancels the payment on the PayPal page.
     * It will redirect to the payment selection.
     */
    public function cancelAction()
    {
        $this->handleError(ErrorCodes::CANCELED_BY_USER);
    }

    /**
     * This method handles the redirection to the shippingPayment action if an
     * error has occurred during the payment process.
     *
     * @see ErrorCodes
     *
     * @param int              $code
     * @param RequestException $exception
     */
    private function handleError($code, RequestException $exception = null)
    {
        /** @var SettingsServiceInterface $settings */
        $settings = $this->container->get('paypal_unified.settings_service');

        /** @var string $message */
        $message = null;
        $name = null;

        if ($exception) {
            $this->logger->error('Received an error during checkout process', ['payload' => $exception->getBody()]);

            //Parse the received error
            $error = ErrorResponse::fromArray(json_decode($exception->getBody(), true));

            if ($error->getMessage() !== null) {
                if ($settings->hasSettings() && $settings->get('display_errors')) {
                    $message = $error->getMessage();
                    $name = $error->getName();
                }
            }

            $genericError = GenericErrorResponse::fromArray(json_decode($exception->getBody(), true));

            if ($genericError->getErrorDescription() !== null) {
                if ($settings->hasSettings() && $settings->get('display_errors')) {
                    $message = $genericError->getErrorDescription();
                    $name = $genericError->getError();
                }
            }
        }

        $this->redirect([
            'controller' => 'checkout',
            'action' => 'shippingPayment',
            'paypal_unified_error_code' => $code,
            'paypal_unified_error_message' => $message,
            'paypal_unified_error_name' => $name,
        ]);
    }

    /**
     * @param string|null $basketId
     *
     * @return bool
     */
    private function validateBasketExtended($basketId = null)
    {
        //Shopware 5.3 installed but no basket id that can be validated.
        if ($basketId === null) {
            return false;
        }

        //New validation for Shopware 5.3.X
        try {
            $basket = $this->loadBasketFromSignature($basketId);
            $this->verifyBasketSignature($basketId, $basket);

            return true;
        } catch (RuntimeException $ex) {
            return false;
        }
    }

    /**
     * @param Payment $payment
     *
     * @return bool
     */
    private function validateBasketSimple(Payment $payment)
    {
        /** @var BasketValidatorInterface $legacyValidator */
        $legacyValidator = $this->container->get('paypal_unified.simple_basket_validator');

        return $legacyValidator->validate($this->getBasket(), $this->getUser(), $payment);
    }
}
