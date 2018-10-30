<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\PaymentAddressService;
use SwagPaymentPayPalUnified\Components\Services\Plus\PaymentInstructionService;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketIdWhitelist;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketValidatorInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PayerInfoPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAddressPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAmountPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentItemsPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentOrderNumberPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\PaymentInstructionType;
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
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var \Shopware_Components_Config
     */
    private $shopwareConfig;

    public function preDispatch()
    {
        $this->paymentResource = $this->get('paypal_unified.payment_resource');
        $this->client = $this->get('paypal_unified.client_service');
        $this->logger = $this->get('paypal_unified.logger_service');
        $this->settingsService = $this->get('paypal_unified.settings_service');
        $this->shopwareConfig = $this->get('config');
    }

    /**
     * Index action of the payment. The only thing to do here is to forward to the gateway action.
     */
    public function indexAction()
    {
        $this->forward('gateway');
    }

    /**
     * The gateway to PayPal. The payment will be created and the user will be redirected to the PayPal site.
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
            $selectedPaymentName = $orderData['sPayment']['name'];

            $requestParams = new PaymentBuilderParameters();
            $requestParams->setBasketData($basketData);
            $requestParams->setUserData($userData);

            //Prepare the new basket signature feature, announced in SW 5.3.0
            if (version_compare($this->shopwareConfig->offsetGet('version'), '5.3.0', '>=')) {
                $basketUniqueId = $this->persistBasket();
                $requestParams->setBasketUniqueId($basketUniqueId);
            }

            /** @var Payment $payment */
            $payment = null;

            // For generic PayPal payments like PayPal or PayPal Plus ones,
            // a different parameter than in installments for the payment creation is needed
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
            $this->handleError(ErrorCodes::UNKNOWN, $exception);

            return;
        }

        //Patch the address data into the payment.
        //This function is only being called for PayPal classic, therefore,
        //there is an additional action (patchAddressAction()) for the PayPal plus integration.
        /** @var PaymentAddressService $addressService */
        $addressService = $this->get('paypal_unified.payment_address_service');
        $addressPatch = new PaymentAddressPatch($addressService->getShippingAddress($userData));
        $payerInfoPatch = new PayerInfoPatch($addressService->getPayerInfo($userData));

        try {
            $this->paymentResource->patch($responseStruct->getId(), [
                $addressPatch,
                $payerInfoPatch,
            ]);
        } catch (\Exception $exception) {
            /*
             * The field addressValidation gets checked via JavaScript to ensure the redirect to the right error page,
             * if the user uses the In-Context mode.
             */
            if ($this->Request()->getParam('useInContext')) {
                $this->Front()->Plugins()->Json()->setRenderer();

                $this->View()->assign('addressValidation', false);

                return;
            }

            $this->handleError(ErrorCodes::ADDRESS_VALIDATION_ERROR, $exception);

            return;
        }

        if ($this->Request()->getParam('useInContext')) {
            $this->Front()->Plugins()->Json()->setRenderer();

            $this->View()->assign('paymentId', $responseStruct->getId());

            return;
        }

        $this->redirect($responseStruct->getLinks()[1]->getHref());
    }

    /**
     * This action is called when the user is being redirected back from PayPal after a successful payment process.
     * The order is saved here in the system and handle the data exchange with PayPal.
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
        $isExpressCheckout = (bool) $request->getParam('expressCheckout', false);
        $isPlus = (bool) $request->getParam('plus', false);
        $isInstallments = (bool) $request->getParam('installments', false);

        try {
            $orderNumber = '';

            /** @var OrderDataService $orderDataService */
            $orderDataService = $this->get('paypal_unified.order_data_service');
            $sendOrderNumber = (bool) $this->settingsService->get('send_order_number');

            if ($isPlus) {
                $this->client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_PLUS);
            } elseif ($isExpressCheckout) {
                $this->client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_EXPRESS_CHECKOUT);
            } elseif ($isInstallments) {
                $this->client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_INSTALLMENTS);
            }

            // if the order number should be send to PayPal do it before the execute
            if ($sendOrderNumber) {
                $orderNumber = $this->saveOrder($paymentId, $paymentId, PaymentStatus::PAYMENT_STATUS_OPEN);
                $patchOrderNumber = $this->settingsService->get('order_number_prefix') . $orderNumber;

                /** @var PaymentOrderNumberPatch $paymentPatch */
                $paymentPatch = new PaymentOrderNumberPatch($patchOrderNumber);

                $this->paymentResource->patch($paymentId, [$paymentPatch]);
            }

            //Basket validation with shopware 5.2 support
            if (in_array($basketId, BasketIdWhitelist::WHITELIST_IDS, true) || version_compare($this->shopwareConfig->get('version'), '5.3.0', '<')) {
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
            if ($paymentState === PaymentStatus::PAYMENT_COMPLETED &&
                !$orderDataService->applyPaymentStatus($orderNumber, PaymentStatus::PAYMENT_STATUS_APPROVED)
            ) {
                $this->handleError(ErrorCodes::NO_ORDER_TO_PROCESS);

                return;
            }

            //Use TXN-ID instead of the PaymentId
            $saleId = $responseSale->getId();
            if (!$orderDataService->applyTransactionId($orderNumber, $saleId)) {
                $this->handleError(ErrorCodes::NO_ORDER_TO_PROCESS);

                return;
            }

            // Save payment instructions from PayPal to database.
            // if the instruction is of type MANUAL_BANK_TRANSFER the instructions are not required,
            // since they don't have to be displayed on the invoice document
            $instructions = $response->getPaymentInstruction();
            if ($instructions && $instructions->getType() === PaymentInstructionType::INVOICE) {
                /** @var PaymentInstructionService $instructionService */
                $instructionService = $this->get('paypal_unified.payment_instruction_service');
                $instructionService->createInstructions($orderNumber, $instructions);
            }

            $orderDataService->applyPaymentTypeAttribute($orderNumber, $response, $isExpressCheckout);

            $redirectParameter = [
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $paymentId,
            ];

            if ($isExpressCheckout) {
                $redirectParameter['expressCheckout'] = true;
            }

            // Done, redirect to the finish page
            $this->redirect($redirectParameter);
        } catch (RequestException $exception) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $exception);
        } catch (\Exception $exception) {
            $this->handleError(ErrorCodes::UNKNOWN, $exception);
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
        $orderData = $this->get('session')->get('sOrderVariables');
        $userData = $orderData['sUserData'];
        $basketData = $orderData['sBasket'];

        /** @var PaymentAddressService $addressService */
        $addressService = $this->get('paypal_unified.payment_address_service');
        $addressPatch = new PaymentAddressPatch($addressService->getShippingAddress($userData));
        $payerInfoPatch = new PayerInfoPatch($addressService->getPayerInfo($userData));

        $requestParams = new PaymentBuilderParameters();
        $requestParams->setBasketData($basketData);
        $requestParams->setUserData($userData);
        $paymentStruct = $this->get('paypal_unified.plus.payment_builder_service')->getPayment($requestParams);

        $amountPatch = new PaymentAmountPatch($paymentStruct->getTransactions()->getAmount());
        $itemsPatch = new PaymentItemsPatch($paymentStruct->getTransactions()->getItemList()->getItems());

        try {
            $this->client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_PLUS);
            $this->paymentResource->patch($paymentId, [$addressPatch, $payerInfoPatch, $itemsPatch, $amountPatch]);
        } catch (Exception $e) {
            $response = $this->get('paypal_unified.exception_handler_service')->handle($e, 'patch address, payer info, item list and amount');

            /*
             * The two response codes are used to differ the error via the ajax call.
             */
            if ($response->getName() === 'VALIDATION_ERROR') {
                $this->Response()->setHttpResponseCode(422);

                return;
            }

            $this->Response()->setHttpResponseCode(400);
        }
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
     * @param int       $code
     * @param Exception $exception
     */
    private function handleError($code, Exception $exception = null)
    {
        /** @var string $message */
        $message = null;
        $name = null;

        if ($exception) {
            /** @var ExceptionHandlerServiceInterface $exceptionHandler */
            $exceptionHandler = $this->get('paypal_unified.exception_handler_service');
            $error = $exceptionHandler->handle($exception, 'process checkout');

            if ($this->settingsService->hasSettings() && $this->settingsService->get('display_errors')) {
                $message = $error->getMessage();
                $name = $error->getName();
            }
        }

        $redirectData = [
            'controller' => 'checkout',
            'action' => 'shippingPayment',
            'paypal_unified_error_code' => $code,
        ];

        if ($name !== null) {
            $redirectData['paypal_unified_error_name'] = $name;
            $redirectData['paypal_unified_error_message'] = $message;
        }

        $this->redirect($redirectData);
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
        $legacyValidator = $this->get('paypal_unified.simple_basket_validator');

        return $legacyValidator->validate($this->getBasket(), $this->getUser(), $payment);
    }
}
