<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\HttpClient\RequestException;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\OrderNumberService;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\ErrorMessages\ErrorMessageTransporter;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\PaymentAddressService;
use SwagPaymentPayPalUnified\Components\Services\Plus\PaymentInstructionService;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketIdWhitelist;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketValidatorInterface;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PayerInfoPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAddressPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAmountPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentItemsPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentOrderNumberPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\PaymentInstructionType;
use Symfony\Component\HttpFoundation\Response;

class Shopware_Controllers_Frontend_PaypalUnified extends Shopware_Controllers_Frontend_Payment
{
    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var PaymentResource
     */
    private $paymentResource;

    /**
     * @var ClientService
     */
    private $client;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @var OrderNumberService
     */
    private $orderNumberService;

    /**
     * @var ErrorMessageTransporter
     */
    private $errorMessageTransporter;

    public function preDispatch()
    {
        $this->dependencyProvider = $this->get('paypal_unified.dependency_provider');
        $this->paymentResource = $this->get('paypal_unified.payment_resource');
        $this->client = $this->get('paypal_unified.client_service');
        $this->settingsService = $this->get('paypal_unified.settings_service');
        $this->shopwareConfig = $this->get('config');
        $this->orderNumberService = $this->get('paypal_unified.order_number_service');
        $this->errorMessageTransporter = $this->get('paypal_unified.error_message_transporter');
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
        $session = $this->dependencyProvider->getSession();
        $orderData = $session->get('sOrderVariables');

        if ($orderData === null) {
            $this->handleError(ErrorCodes::NO_ORDER_TO_PROCESS);

            return;
        }

        if ($this->noDispatchForOrder()) {
            $this->handleError(ErrorCodes::NO_DISPATCH_FOR_ORDER);

            return;
        }

        $userData = $orderData['sUserData'];
        $userData[CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES] = (bool) $session->get('sUserGroupData', ['tax' => 1])['tax'];

        try {
            // Query all information
            $basketData = $orderData['sBasket'];

            $requestParams = new PaymentBuilderParameters();
            $requestParams->setBasketData($basketData);
            $requestParams->setUserData($userData);
            $requestParams->setPaymentToken($this->dependencyProvider->createPaymentToken());

            // If supported, add the basket signature feature
            if ($this->container->initialized('basket_signature_generator')) {
                $basketUniqueId = $this->persistBasket();
                $requestParams->setBasketUniqueId($basketUniqueId);
            }

            $requestParams->setPaymentType(PaymentType::PAYPAL_CLASSIC);
            $payment = $this->get('paypal_unified.payment_builder_service')->getPayment($requestParams);

            $response = $this->paymentResource->create($payment);

            $responseStruct = Payment::fromArray($response);
        } catch (RequestException $requestEx) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $requestEx);

            return;
        } catch (Exception $exception) {
            $this->handleError(ErrorCodes::UNKNOWN, $exception);

            return;
        }

        // Patch the address data into the payment.
        // This function is only being called for PayPal classic, therefore,
        // there is an additional action (patchAddressAction()) for the PayPal plus integration.
        /** @var PaymentAddressService $addressService */
        $addressService = $this->get('paypal_unified.payment_address_service');
        $addressPatch = new PaymentAddressPatch($addressService->getShippingAddress($userData));
        $payerInfoPatch = new PayerInfoPatch($addressService->getPayerInfo($userData));

        $this->Front()->Plugins()->Json()->setRenderer();
        $this->View()->setTemplate();

        try {
            $this->paymentResource->patch($responseStruct->getId(), [
                $addressPatch,
                $payerInfoPatch,
            ]);
        } catch (Exception $exception) {
            $this->handleError(ErrorCodes::ADDRESS_VALIDATION_ERROR, $exception);

            return;
        }

        $this->View()->assign('paymentId', $responseStruct->getId());
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
        $basketId = $request->getParam('basketId');

        // Basket validation with shopware 5.2 support
        if (\in_array($basketId, BasketIdWhitelist::WHITELIST_IDS, true)
            || !$this->container->initialized('basket_signature_generator')
        ) {
            // For shopware < 5.3 and for whitelisted basket ids
            try {
                $payment = $this->paymentResource->get($paymentId);
            } catch (RequestException $exception) {
                $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $exception);

                return;
            }

            $basketValid = $this->validateBasketSimple(Payment::fromArray($payment));
        } else {
            // For shopware > 5.3
            $basketValid = $this->validateBasketExtended($basketId);
        }

        if (!$basketValid) {
            $this->handleError(ErrorCodes::BASKET_VALIDATION_ERROR);

            return;
        }

        $isPlus = (bool) $request->getParam('plus', false);
        $isExpressCheckout = (bool) $request->getParam('expressCheckout', false);
        $isSpbCheckout = (bool) $request->getParam('spbCheckout', false);

        if ($isPlus) {
            $this->client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_PLUS);
        } elseif ($isExpressCheckout) {
            $this->client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_EXPRESS_CHECKOUT);
        } elseif ($isSpbCheckout) {
            $this->client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_SMART_PAYMENT_BUTTONS);
        }

        $shopwareOrderNumber = $this->orderNumberService->getOrderNumber();
        $patchOrderNumber = $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_ORDER_NUMBER_PREFIX) . $shopwareOrderNumber;

        $paymentPatch = new PaymentOrderNumberPatch($patchOrderNumber);

        try {
            $this->paymentResource->patch($paymentId, [$paymentPatch]);
        } catch (RequestException $exception) {
            $this->orderNumberService->restoreOrdernumberToPool();
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $exception);

            return;
        }

        $payerId = $request->getParam('PayerID');
        /** @var OrderDataService $orderDataService */
        $orderDataService = $this->get('paypal_unified.order_data_service');

        try {
            // execute the payment to the PayPal API
            $executionResponse = $this->paymentResource->execute($payerId, $paymentId);
            if ($executionResponse === null) {
                $this->orderNumberService->restoreOrdernumberToPool();
                $this->handleError(ErrorCodes::COMMUNICATION_FAILURE);

                return;
            }
        } catch (RequestException $exception) {
            $exceptionBody = json_decode($exception->getBody(), true);
            if ($exceptionBody['name'] === 'DUPLICATE_TRANSACTION') {
                $this->orderNumberService->releaseOrderNumber();

                $this->redirect($this->getResetActionParameter($request));

                return;
            }

            $errorCode = ErrorCodes::COMMUNICATION_FAILURE;
            $this->orderNumberService->restoreOrdernumberToPool();
            $this->handleError($errorCode, $exception);

            return;
        }

        $response = Payment::fromArray($executionResponse);
        $request->setParam('invoiceCheckout', $response->getPaymentInstruction() !== null);

        $this->saveOrder($paymentId, $paymentId, Status::PAYMENT_STATE_OPEN);
        $this->orderNumberService->releaseOrderNumber();

        $relatedResource = $response->getTransactions()->getRelatedResources()->getResources()[0];

        // Use TXN-ID instead of the PaymentId
        $relatedResourceId = $relatedResource->getId();
        if (!$orderDataService->applyTransactionId($shopwareOrderNumber, $relatedResourceId)) {
            $this->orderNumberService->restoreOrdernumberToPool();
            $this->handleError(ErrorCodes::NO_ORDER_TO_PROCESS);

            return;
        }

        // apply the payment status if its completed by PayPal
        $paymentState = $relatedResource->getState();
        if ($paymentState === PaymentStatus::PAYMENT_COMPLETED) {
            $this->savePaymentStatus($relatedResourceId, $paymentId, Status::PAYMENT_STATE_COMPLETELY_PAID);
            $orderDataService->setClearedDate($shopwareOrderNumber);
        }

        // Save payment instructions from PayPal to database.
        // if the instruction is of type MANUAL_BANK_TRANSFER the instructions are not required,
        // since they don't have to be displayed on the invoice document
        $instructions = $response->getPaymentInstruction();
        if ($instructions && $instructions->getType() === PaymentInstructionType::INVOICE) {
            /** @var PaymentInstructionService $instructionService */
            $instructionService = $this->get('paypal_unified.payment_instruction_service');
            $instructionService->createInstructions($shopwareOrderNumber, $instructions);
        }

        $paymentType = $this->determinePaymentType($isExpressCheckout, $isSpbCheckout, $response);

        $orderDataService->applyPaymentTypeAttribute($shopwareOrderNumber, $paymentType);

        $redirectParameter = [
            'module' => 'frontend',
            'controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $paymentId,
        ];

        if ($isExpressCheckout) {
            $redirectParameter['expressCheckout'] = true;
        }

        $this->dependencyProvider->getSession()->offsetUnset('sComment');

        // Done, redirect to the finish page
        $this->redirect($redirectParameter);
    }

    /**
     * This action is being called via Ajax by the PayPal-Plus integration only.
     * Required parameters:
     *  (string) paymentId
     */
    public function patchAddressAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        if ($this->noDispatchForOrder()) {
            $response = $this->Response();
            $response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
            $response->setBody(ErrorCodes::NO_DISPATCH_FOR_ORDER);

            return;
        }

        $request = $this->Request();
        $session = $this->dependencyProvider->getSession();

        $paymentId = $request->getParam('paymentId');
        $orderData = $session->get('sOrderVariables');
        $userData = $orderData['sUserData'];
        $basketData = $orderData['sBasket'];
        $userData[CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES] = (bool) $session->get('sUserGroupData', ['tax' => 1])['tax'];

        $customerComment = (string) $request->getParam('customerComment', '');
        if ($customerComment !== '') {
            $session->offsetSet('sComment', $customerComment);
        }

        /** @var PaymentAddressService $addressService */
        $addressService = $this->get('paypal_unified.payment_address_service');
        $addressPatch = new PaymentAddressPatch($addressService->getShippingAddress($userData));
        $payerInfoPatch = new PayerInfoPatch($addressService->getPayerInfo($userData));

        $requestParams = new PaymentBuilderParameters();
        $requestParams->setBasketData($basketData);
        $requestParams->setUserData($userData);
        $requestParams->setPaymentType(PaymentType::PAYPAL_PLUS);
        $paymentStruct = $this->get('paypal_unified.plus.payment_builder_service')->getPayment($requestParams);

        $amountPatch = new PaymentAmountPatch($paymentStruct->getTransactions()->getAmount());
        $patches = [$addressPatch, $payerInfoPatch, $amountPatch];

        $itemList = $paymentStruct->getTransactions()->getItemList();
        if ($itemList !== null) {
            $patches[] = new PaymentItemsPatch($itemList->getItems());
        }

        try {
            $this->client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_PLUS);
            $this->paymentResource->patch($paymentId, $patches);
        } catch (Exception $exception) {
            $response = $this->get('paypal_unified.exception_handler_service')->handle(
                $exception,
                'patch address, payer info, item list and amount'
            );

            $this->Response()->setHttpResponseCode(Response::HTTP_BAD_REQUEST);

            /*
             * The two response codes are used to differ the error via the ajax call.
             */
            if ($response->getName() === 'VALIDATION_ERROR') {
                $this->Response()->setBody(ErrorCodes::ADDRESS_VALIDATION_ERROR);
            }
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
     * This method handles the redirection to the shippingPayment action if an error has occurred during the payment process.
     * If the order number was sent before, the method will redirect to the finish page.
     *
     * @param int  $code
     * @param bool $redirectToFinishAction
     *
     * @see ErrorCodes
     */
    private function handleError($code, Exception $exception = null, $redirectToFinishAction = false)
    {
        /** @var string $message */
        $message = null;
        $name = null;

        if ($exception) {
            /** @var ExceptionHandlerServiceInterface $exceptionHandler */
            $exceptionHandler = $this->get('paypal_unified.exception_handler_service');
            $error = $exceptionHandler->handle($exception, 'process checkout');

            if ($this->settingsService->hasSettings() && $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_DISPLAY_ERRORS)) {
                $message = $error->getMessage();
                $name = $error->getName();
            }
        }

        if ($this->Request()->isXmlHttpRequest()) {
            $this->Front()->Plugins()->Json()->setRenderer();
            $view = $this->View();
            $view->setTemplate();

            $view->assign('errorCode', $code);
            if ($name !== null) {
                $view->assign([
                    'paypal_unified_error_name' => $name,
                    'paypal_unified_error_message' => $message,
                ]);
            }

            return;
        }

        $redirectData = [
            'controller' => 'checkout',
            'action' => 'shippingPayment',
            'paypal_unified_error_code' => $code,
        ];

        if ($redirectToFinishAction) {
            $redirectData['action'] = 'finish';
        }

        if (\is_string($name) && \is_string($message)) {
            $redirectData[RedirectDataBuilder::PAYPAL_UNIFIED_ERROR_KEY] = $this->errorMessageTransporter->setErrorMessageToSession($name, $message);
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
        // Shopware 5.3 installed but no basket id that can be validated.
        if ($basketId === null) {
            return false;
        }

        // New validation for Shopware 5.3.X
        try {
            $basket = $this->loadBasketFromSignature($basketId);
            $this->verifyBasketSignature($basketId, $basket);

            return true;
        } catch (RuntimeException $ex) {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function validateBasketSimple(Payment $payment)
    {
        /** @var BasketValidatorInterface $legacyValidator */
        $legacyValidator = $this->get('paypal_unified.simple_basket_validator');

        $basket = $this->getBasket();
        $customer = $this->getUser();
        if ($basket === null || $customer === null) {
            return false;
        }

        return $legacyValidator->validate($this->getBasket(), $this->getUser(), (float) $payment->getTransactions()->getAmount()->getTotal());
    }

    /**
     * @return bool
     */
    private function noDispatchForOrder()
    {
        $session = $this->dependencyProvider->getSession();

        return !empty($this->shopwareConfig->get('premiumShippingNoOrder')) && (empty($session->get('sDispatch')) || empty($session->get('sCountry')));
    }

    /**
     * @param bool $isExpressCheckout
     * @param bool $isSpbCheckout
     *
     * @return string
     */
    private function determinePaymentType($isExpressCheckout, $isSpbCheckout, Payment $response)
    {
        $paymentType = PaymentType::PAYPAL_CLASSIC;

        if ($isExpressCheckout) {
            $paymentType = PaymentType::PAYPAL_EXPRESS;
        } elseif ($isSpbCheckout) {
            $paymentType = PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS;
        } elseif ($response->getPaymentInstruction() !== null) {
            $paymentType = PaymentType::PAYPAL_INVOICE;
        } elseif ((bool) $this->settingsService->get('active', SettingsTable::PLUS)) {
            $paymentType = PaymentType::PAYPAL_PLUS;
        }

        return $paymentType;
    }

    /**
     * @param Enlight_Controller_Request_RequestHttp $request
     *
     * @return array<string,mixed>
     */
    private function getResetActionParameter($request)
    {
        return [
            'module' => 'frontend',
            'controller' => 'PaypalUnified',
            'action' => 'return',
            'paymentId' => $request->getParam('paymentId'),
            'basketId' => $request->getParam('basketId'),
            'PayerID' => $request->getParam('PayerID'),
            'plus' => (bool) $request->getParam('plus', false),
            'expressCheckout' => (bool) $request->getParam('expressCheckout', false),
            'spbCheckout' => (bool) $request->getParam('spbCheckout', false),
            'customerComment' => (string) $request->getParam('customerComment', ''),
        ];
    }
}
