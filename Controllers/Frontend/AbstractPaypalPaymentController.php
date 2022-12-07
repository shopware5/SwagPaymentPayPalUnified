<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Controllers\Frontend;

use Exception;
use PDO;
use RuntimeException;
use sAdmin;
use Shopware\Components\HttpClient\RequestException;
use Shopware\Models\Order\Status;
use Shopware_Components_Config;
use Shopware_Controllers_Frontend_Payment;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\Exception\OrderNotFoundException;
use SwagPaymentPayPalUnified\Components\Exception\PuiValidationException;
use SwagPaymentPayPalUnified\Components\OrderNumberService;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacade;
use SwagPaymentPayPalUnified\Components\Services\DispatchValidation;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\OrderPropertyHelper;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\Components\Services\Validation\SimpleBasketValidator;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentControllerResults\DeterminedStatus;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\AuthorizationDeniedException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\CaptureDeclinedException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\CaptureFailedException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InstrumentDeclinedException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InvalidBillingAddressException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InvalidShippingAddressException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\NoOrderToProceedException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\PayerActionRequiredException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\RequireRestartException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\UnexpectedStatusException;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderAddInvoiceIdPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use UnexpectedValueException;

class AbstractPaypalPaymentController extends Shopware_Controllers_Frontend_Payment
{
    const MAXIMUM_RETRIES = 48;
    const TIMEOUT = \CURLOPT_TIMEOUT * 2;
    const SLEEP = 2;
    const PAYMENT_SOURCE_GETTER_LIST = [
        PaymentType::PAYPAL_PAY_UPON_INVOICE_V2 => 'getPayUponInvoice',
        PaymentType::APM_BANCONTACT => 'getBancontact',
        PaymentType::APM_BLIK => 'getBlik',
        PaymentType::APM_GIROPAY => 'getGiropay',
        PaymentType::APM_IDEAL => 'getIdeal',
        PaymentType::APM_MULTIBANCO => 'getMultibanco',
        PaymentType::APM_MYBANK => 'getMybank',
        PaymentType::APM_P24 => 'getP24',
        PaymentType::APM_SOFORT => 'getSofort',
        PaymentType::APM_TRUSTLY => 'getTrustly',
        PaymentType::APM_EPS => 'getEps',
    ];

    const UNPROCESSABLE_ENTITY = 'UNPROCESSABLE_ENTITY';
    const PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED = 'PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED';
    const PAYMENT_SOURCE_DECLINED_BY_PROCESSOR = 'PAYMENT_SOURCE_DECLINED_BY_PROCESSOR';

    const COMMENT_KEY = 'sComment';
    const NEWSLETTER_KEY = 'sNewsletter';

    /**
     * @var DependencyProvider
     */
    protected $dependencyProvider;

    /**
     * @var RedirectDataBuilderFactory
     */
    protected $redirectDataBuilderFactory;

    /**
     * @var PaymentControllerHelper
     */
    protected $paymentControllerHelper;

    /**
     * @var DispatchValidation
     */
    protected $dispatchValidator;

    /**
     * @var PayPalOrderParameterFacade
     */
    protected $payPalOrderParameterFacade;

    /**
     * @var OrderResource
     */
    protected $orderResource;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var SettingsServiceInterface
     */
    protected $settingsService;

    /**
     * @var OrderDataService
     */
    protected $orderDataService;

    /**
     * @var PaymentMethodProviderInterface
     */
    protected $paymentMethodProvider;

    /**
     * @var ExceptionHandlerService
     */
    protected $exceptionHandler;

    /**
     * @var Shopware_Components_Config
     */
    protected $shopwareConfig;

    /**
     * @var PaymentStatusService
     */
    protected $paymentStatusService;

    /**
     * @var LoggerServiceInterface
     */
    protected $logger;

    /**
     * @var OrderPropertyHelper
     */
    protected $orderPropertyHelper;

    /**
     * @var SimpleBasketValidator
     */
    protected $simpleBasketValidator;

    /**
     * @var OrderNumberService
     */
    protected $orderNumberService;

    public function preDispatch()
    {
        $this->dependencyProvider = $this->get('paypal_unified.dependency_provider');
        $this->redirectDataBuilderFactory = $this->get('paypal_unified.redirect_data_builder_factory');
        $this->paymentControllerHelper = $this->get('paypal_unified.payment_controller_helper');
        $this->dispatchValidator = $this->get('paypal_unified.dispatch_validation');
        $this->payPalOrderParameterFacade = $this->get('paypal_unified.paypal_order_parameter_facade');
        $this->orderResource = $this->get('paypal_unified.v2.order_resource');
        $this->orderFactory = $this->get('paypal_unified.order_factory');
        $this->settingsService = $this->get('paypal_unified.settings_service');
        $this->orderDataService = $this->get('paypal_unified.order_data_service');
        $this->paymentMethodProvider = $this->get('paypal_unified.payment_method_provider');
        $this->exceptionHandler = $this->get('paypal_unified.exception_handler_service');
        $this->shopwareConfig = $this->get('config');
        $this->paymentStatusService = $this->get('paypal_unified.payment_status_service');
        $this->logger = $this->get('paypal_unified.logger_service');
        $this->orderPropertyHelper = $this->get('paypal_unified.order_property_helper');
        $this->simpleBasketValidator = $this->get('paypal_unified.simple_basket_validator');
        $this->orderNumberService = $this->get('paypal_unified.order_number_service');
    }

    /**
     * @return void
     */
    public function cancelAction()
    {
        $shopwareErrorCode = ErrorCodes::CANCELED_BY_USER;
        $paypalErrorCode = $this->request->getParam('errorcode');

        if ($paypalErrorCode === 'processing_error') {
            $shopwareErrorCode = ErrorCodes::COMMUNICATION_FAILURE;
        }

        $this->logger->debug(sprintf('%s CANCELED_BY_USER', __METHOD__));

        $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
            ->setCode($shopwareErrorCode);

        $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);
    }

    /**
     * @return Order|null
     */
    protected function createPayPalOrder(PayPalOrderParameter $orderParameter)
    {
        try {
            $this->logger->debug(sprintf('%s BEFORE CREATE PAYPAL ORDER', __METHOD__));

            $payPalOrderData = $this->orderFactory->createOrder($orderParameter);

            $payPalOrder = $this->orderResource->create($payPalOrderData, $orderParameter->getPaymentType());

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFUL CREATED - ID: %d', __METHOD__, $payPalOrder->getId()));
        } catch (RequestException $exception) {
            $exceptionBody = json_decode($exception->getBody(), true);

            foreach ($exceptionBody['details'] as $exceptionDetail) {
                if ($exceptionDetail['issue'] === 'BILLING_ADDRESS_INVALID') {
                    $this->orderNumberService->releaseOrderNumber();

                    throw new InvalidBillingAddressException();
                }
            }

            foreach ($exceptionBody['details'] as $exceptionDetail) {
                if ($exceptionDetail['issue'] === 'SHIPPING_ADDRESS_INVALID') {
                    $this->orderNumberService->releaseOrderNumber();

                    throw new InvalidShippingAddressException();
                }
            }

            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode($this->getErrorCode($exception->getBody()))
                ->setException($exception, 'create PayPal order');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return null;
        } catch (PuiValidationException $puiValidationException) {
            throw $puiValidationException;
        } catch (Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception, 'create PayPal order');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return null;
        }

        return $payPalOrder;
    }

    /**
     * @param string           $payPalOrderId
     * @param array<int,Patch> $patches
     *
     * @return bool
     */
    protected function updatePayPalOrder($payPalOrderId, array $patches)
    {
        try {
            $this->logger->debug(sprintf('%s UPDATE PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));

            $this->orderResource->update($patches, $payPalOrderId);

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY UPDATED', __METHOD__));
        } catch (RequestException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode($this->getErrorCode($exception->getBody()))
                ->setException($exception, 'update PayPal order');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return false;
        } catch (Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception, 'update PayPal order');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return false;
        }

        return true;
    }

    /**
     * @param Order $payPalOrder
     *
     * @return Order
     */
    protected function captureOrAuthorizeOrder($payPalOrder)
    {
        if ($payPalOrder->getStatus() === PaymentStatusV2::ORDER_COMPLETED) {
            return $payPalOrder;
        }

        try {
            if ($payPalOrder->getIntent() === PaymentIntentV2::CAPTURE) {
                $this->logger->debug(sprintf('%s CAPTURE PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrder->getId()));

                $capturedPayPalOrder = $this->orderResource->capture($payPalOrder->getId(), false);

                $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY CAPTURED', __METHOD__));

                return $capturedPayPalOrder;
            } elseif ($payPalOrder->getIntent() === PaymentIntentV2::AUTHORIZE) {
                $this->logger->debug(sprintf('%s AUTHORIZE PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrder->getId()));

                $authorizedPayPalOrder = $this->orderResource->authorize($payPalOrder->getId(), false);

                $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY AUTHORIZED', __METHOD__));

                return $authorizedPayPalOrder;
            }
        } catch (RequestException $exception) {
            $exceptionBody = json_decode($exception->getBody(), true);

            foreach ($exceptionBody['details'] as $exceptionDetail) {
                if ($exceptionDetail['issue'] === 'DUPLICATE_INVOICE_ID') {
                    $this->orderNumberService->releaseOrderNumber();

                    throw new RequireRestartException();
                }

                if ($exceptionDetail['issue'] === 'PAYER_ACTION_REQUIRED') {
                    $this->orderNumberService->releaseOrderNumber();

                    throw new PayerActionRequiredException();
                }

                if ($exceptionDetail['issue'] === 'INSTRUMENT_DECLINED') {
                    $this->orderNumberService->releaseOrderNumber();

                    throw new InstrumentDeclinedException();
                }
            }

            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                ->setException($exception, 'capture/authorize PayPal order');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            throw new NoOrderToProceedException();
        } catch (Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception, 'capture/authorize PayPal order');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            throw new NoOrderToProceedException();
        }

        throw new NoOrderToProceedException();
    }

    /**
     * @return bool Capture/Authorization was successful
     */
    protected function checkCaptureAuthorizationStatus(Order $payPalOrder)
    {
        $this->logger->debug(sprintf('%s PAYPAL CHECK CAPTURE OR AUTHORIZATION STATUS START', __METHOD__));

        try {
            if ($payPalOrder->getIntent() === PaymentIntentV2::CAPTURE) {
                $capture = $this->orderPropertyHelper->getFirstCapture($payPalOrder);
                if (!$capture instanceof Capture) {
                    throw new UnexpectedValueException(sprintf('%s expected. Got %s', Capture::class, \gettype($capture)));
                }

                if ($capture->getStatus() === PaymentStatusV2::ORDER_CAPTURE_DECLINED) {
                    throw new CaptureDeclinedException();
                }

                if ($capture->getStatus() === PaymentStatusV2::ORDER_CAPTURE_FAILED) {
                    throw new CaptureFailedException();
                }
            }

            if ($payPalOrder->getIntent() === PaymentIntentV2::AUTHORIZE) {
                $authorization = $this->orderPropertyHelper->getAuthorization($payPalOrder);
                if (!$authorization instanceof Authorization) {
                    throw new UnexpectedValueException(sprintf('%s expected. Got %s', Authorization::class, \gettype($authorization)));
                }

                if ($authorization->getStatus() === PaymentStatusV2::ORDER_AUTHORIZATION_DENIED) {
                    throw new AuthorizationDeniedException();
                }
            }
        } catch (UnexpectedValueException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception, 'Check capture/authorize status');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return false;
        } catch (UnexpectedStatusException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode($exception->getCode())
                ->setException($exception, 'Check capture/authorize status');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return false;
        }

        $this->logger->debug(sprintf('%s PAYPAL CHECK CAPTURE OR AUTHORIZATION STATUS END', __METHOD__));

        return true;
    }

    /**
     * @param string $payPalOrderId
     *
     * @return Order|null
     */
    protected function getPayPalOrder($payPalOrderId)
    {
        try {
            $this->logger->debug(sprintf('%s GET PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));

            $payPalOrder = $this->orderResource->get($payPalOrderId);

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY LOADED', __METHOD__));
        } catch (RequestException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode($this->getErrorCode($exception->getBody()))
                ->setException($exception, 'get PayPal order');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return null;
        } catch (Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception, 'get PayPal order');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return null;
        }

        return $payPalOrder;
    }

    /**
     * @return PaymentType::*
     */
    protected function getPaymentType()
    {
        $customer = $this->getUser();

        if (!\is_array($customer)) {
            return PaymentType::PAYPAL_CLASSIC_V2;
        }

        $paymentName = $this->paymentMethodProvider->getPaymentNameById($customer['additional']['payment']['id']);
        if (!\is_string($paymentName)) {
            return PaymentType::PAYPAL_CLASSIC_V2;
        }

        try {
            $paymentType = $this->paymentMethodProvider->getPaymentTypeByName($paymentName);
        } catch (UnexpectedValueException $exception) {
            // In this case it is a payment method that does not come from the PayPalPlugin.
            // Since this should not happen, we return the classic payment method.
            return PaymentType::PAYPAL_CLASSIC_V2;
        }

        return $paymentType;
    }

    /**
     * @param string $paymentMethodName
     *
     * @return PaymentType::*
     */
    protected function getPaymentTypeByName($paymentMethodName)
    {
        return $this->paymentMethodProvider->getPaymentTypeByName($paymentMethodName);
    }

    /**
     * @param string|null $cartId
     *
     * @return bool
     */
    protected function shouldUseExtendedBasketValidator($cartId = null)
    {
        if (!$cartId || $cartId === 'null') {
            return false;
        }

        if ($cartId === 'express') {
            return false;
        }

        if ($this->container->initialized('basket_signature_generator')) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isCartValid(Order $payPalOrder)
    {
        $cartId = $this->Request()->getParam('basketId');

        if ($this->shouldUseExtendedBasketValidator($cartId)) {
            return $this->validateBasketExtended($cartId);
        }

        return $this->validateBasketSimple($payPalOrder);
    }

    /**
     * @param string $cartId
     *
     * @return bool
     */
    protected function validateBasketExtended($cartId)
    {
        try {
            $cart = $this->loadBasketFromSignature($cartId);
            $this->verifyBasketSignature($cartId, $cart);

            return true;
        } catch (RuntimeException $ex) {
            return false;
        }
    }

    /**
     * @return bool
     */
    protected function validateBasketSimple(Order $payPalOrder)
    {
        $cart = $this->getBasket();
        $customer = $this->getUser();

        if ($cart === null || $customer === null) {
            return false;
        }

        foreach ($payPalOrder->getPurchaseUnits() as $purchaseUnit) {
            if (!$this->simpleBasketValidator->validate($cart, $customer, (float) $purchaseUnit->getAmount()->getValue())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $relationKey See: \SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common\Link
     *
     * @return string
     */
    protected function getUrl(Order $payPalOrder, $relationKey)
    {
        $url = null;
        foreach ($payPalOrder->getLinks() as $link) {
            if ($link->getRel() === $relationKey) {
                $url = $link->getHref();
            }
        }

        if ($url === null) {
            throw new RuntimeException('No link for redirect found');
        }

        return $url;
    }

    /**
     * @param string $payPalOrderId
     * @param string $awaitedStatus
     *
     * @return bool indicating whether the payment is complete
     */
    protected function isPaymentCompleted($payPalOrderId, $awaitedStatus = PaymentStatusV2::ORDER_CAPTURE_COMPLETED)
    {
        $methodStart = microtime(true);
        $this->logger->debug(sprintf('%s START POLLING AT: %f', __METHOD__, microtime(true)));

        for ($i = 0; $i <= static::MAXIMUM_RETRIES; ++$i) {
            $this->logger->debug(sprintf('%s POLLING TRY NR: %d AT: %f', __METHOD__, $i, microtime(true)));

            $timeoutStart = microtime(true);
            $paypalOrder = $this->getPayPalOrder($payPalOrderId);
            if (!$paypalOrder instanceof Order) {
                $this->logger->error(sprintf('%s CANNOT FIND ORDER WITH ID: %s AT TRY NR: %d', __METHOD__, $payPalOrderId, $i));
                break;
            }

            $determinedStatus = $this->determineStatus($paypalOrder, $awaitedStatus);
            if ($determinedStatus->isSuccess()) {
                $currentTime = microtime(true);
                $elapsedTime = $currentTime - $timeoutStart;
                $this->logger->debug(sprintf('%s POLLING SUCCESSFUL AFTER TRY NR: %d AT: %f AFTER: %f SECONDS', __METHOD__, $i, $currentTime, $elapsedTime));

                return true;
            }

            if (time() >= $timeoutStart + static::TIMEOUT) {
                $elapsedTime = microtime(true) - $timeoutStart;

                $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                    ->setCode(ErrorCodes::UNKNOWN)
                    ->setException(new RuntimeException('Timeout exceeded.'), \sprintf('Determine is payment completed. Timout exceeded after %s seconds.', $elapsedTime));

                $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

                break;
            }

            if ($i >= static::MAXIMUM_RETRIES) {
                $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                    ->setCode(ErrorCodes::UNKNOWN)
                    ->setException(new RuntimeException('Maximum retries exceeded.'), \sprintf('Determine is payment completed. Maximum retries exceeded after: %d retries.', $i));

                $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

                break;
            }

            if ($determinedStatus->isPaymentFailed()) {
                $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                    ->setCode(ErrorCodes::UNKNOWN)
                    ->setException(new RuntimeException('Order has been voided.'), 'Payment failed');

                $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

                break;
            }

            sleep(static::SLEEP);
        }

        $currentTime = microtime(true);
        $elapsedTime = $currentTime - $methodStart;
        $this->logger->debug(sprintf('%s POLLING FAILED AT: %f AFTER: %f SECONDS AND TRY NR: %d', __METHOD__, $currentTime, $elapsedTime, $i));

        return false;
    }

    /**
     * @param string                       $payPalOrderId
     * @param PaymentType::*               $paymentType
     * @param Status::PAYMENT_STATE_*|null $paymentStatusId
     *
     * @return string
     */
    protected function createShopwareOrder($payPalOrderId, $paymentType, $paymentStatusId = null)
    {
        $this->logger->debug(sprintf('%s CREATE SHOPWARE ORDER', __METHOD__));

        $orderNumber = (string) $this->saveOrder($payPalOrderId, $payPalOrderId, $paymentStatusId ?: Status::PAYMENT_STATE_OPEN);

        $this->orderDataService->applyPaymentTypeAttribute($orderNumber, $paymentType);

        $this->logger->debug(sprintf('%s ORDER SUCCESSFUL CREATED WITH NUMBER: %s', __METHOD__, $orderNumber));

        $this->orderNumberService->releaseOrderNumber();

        return $orderNumber;
    }

    /**
     * @param string $shopwareOrderNumber
     *
     * @return OrderAddInvoiceIdPatch
     */
    protected function createInvoiceIdPatch($shopwareOrderNumber)
    {
        $orderNumberPrefix = $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_ORDER_NUMBER_PREFIX);

        $invoiceIdPatch = new OrderAddInvoiceIdPatch();
        $invoiceIdPatch->setOp(Patch::OPERATION_ADD);
        $invoiceIdPatch->setValue(sprintf('%s%s', $orderNumberPrefix, $shopwareOrderNumber));
        $invoiceIdPatch->setPath(OrderAddInvoiceIdPatch::PATH);

        return $invoiceIdPatch;
    }

    /**
     * @param string|null $shopwareOrderNumber
     *
     * @return void
     */
    protected function setTransactionId($shopwareOrderNumber, Order $payPalOrder)
    {
        if (!\is_string($shopwareOrderNumber)) {
            $logTemplate = 'Cannot set transactionId because order number is not valid. PayPalOrderId: %s';
            $this->logger->warning(sprintf($logTemplate, $payPalOrder->getId()));

            return;
        }

        $paymentId = null;
        $payments = $payPalOrder->getPurchaseUnits()[0]->getPayments();
        if ($payPalOrder->getIntent() === PaymentIntentV2::CAPTURE) {
            $captures = $payments->getCaptures();
            if (!\is_array($captures)) {
                throw new UnexpectedValueException('PayPal order has intent CAPTURE, but no captures in "payments" object');
            }
            $paymentId = $captures[0]->getId();
        } elseif ($payPalOrder->getIntent() === PaymentIntentV2::AUTHORIZE) {
            $authorizations = $payments->getAuthorizations();
            if (!\is_array($authorizations)) {
                throw new UnexpectedValueException('PayPal order has intent AUTHORIZE, but no authorizations in "payments" object');
            }
            $paymentId = $authorizations[0]->getId();
        }

        if (!\is_string($paymentId)) {
            return;
        }

        $this->orderDataService->applyTransactionId($shopwareOrderNumber, $paymentId);
    }

    /**
     * @return void
     */
    protected function handleComment()
    {
        $session = $this->dependencyProvider->getSession();
        $sComment = $this->request->get(self::COMMENT_KEY, '');

        if (empty($sComment) && $session->offsetExists(self::COMMENT_KEY) && \is_string($session->offsetGet(self::COMMENT_KEY))) {
            $sComment = $session->offsetGet(self::COMMENT_KEY);
        }

        if ($sComment === null) {
            $sComment = '';
        }

        $session->offsetSet(self::COMMENT_KEY, $sComment);
        $this->dependencyProvider->getModule('order')->sComment = $sComment;
    }

    /**
     * @return void
     */
    protected function handleNewsletter()
    {
        $sNewsletterRequestValue = (bool) $this->request->get(self::NEWSLETTER_KEY, false);

        if ($sNewsletterRequestValue === true) {
            /** @var sAdmin $sAdmin */
            $sAdmin = $this->dependencyProvider->getModule('admin');

            $userEmail = $sAdmin->sGetUserMailById();
            if ($userEmail === null) {
                $this->logger->error(
                    \sprintf(
                        '%s - CANNOT FIND USER EMAIL BY ID. ID: %d GIVEN',
                        __METHOD__,
                        (int) $this->dependencyProvider->getSession()->offsetGet('sUserId')
                    )
                );

                return;
            }

            $sAdmin->sUpdateNewsletter(true, $userEmail, true);
        }
    }

    /**
     * @param PaymentIntentV2::* $intent
     * @param int                $shopwareOrderId
     *
     * @return void
     */
    protected function updatePaymentStatus($intent, $shopwareOrderId)
    {
        if ($intent === PaymentIntentV2::CAPTURE) {
            $this->paymentStatusService->updatePaymentStatusV2($shopwareOrderId, Status::PAYMENT_STATE_COMPLETELY_PAID);
        } else {
            $this->paymentStatusService->updatePaymentStatusV2($shopwareOrderId, Status::PAYMENT_STATE_RESERVED);
        }
    }

    /**
     * @param string $shopwareOrderNumber
     *
     * @return int
     */
    protected function getOrderId($shopwareOrderNumber)
    {
        $connection = $this->container->get('dbal_connection');
        $shopwareOrderId = $connection->createQueryBuilder()->select(['id'])
            ->from('s_order')
            ->where('ordernumber = :orderNumber')
            ->setParameter('orderNumber', $shopwareOrderNumber)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);

        if (!$shopwareOrderId) {
            throw new OrderNotFoundException('Order number', $shopwareOrderNumber);
        }

        return (int) $shopwareOrderId;
    }

    /**
     * @param array<string,bool> $error
     *
     * @return void
     */
    protected function redirectInvalidAddress(array $error)
    {
        $errorKey = $this->evaluateAddressError($error);

        $this->logger->debug(\sprintf('%s - %s', __METHOD__, $errorKey));

        $this->redirect(array_merge([
            'module' => 'frontend',
            'controller' => 'address',
            'action' => 'index',
        ], $error));
    }

    /**
     * @param array<string,bool> $error
     *
     * @return string
     */
    protected function getInvalidAddressUrl(array $error)
    {
        $errorKey = $this->evaluateAddressError($error);

        $this->logger->debug(\sprintf('%s - %s', __METHOD__, $errorKey));

        return $this->front->Router()->assemble(array_merge([
            'module' => 'frontend',
            'controller' => 'address',
            'action' => 'index',
        ], $error));
    }

    /**
     * @param array<string,bool> $error
     *
     * @return string
     */
    private function evaluateAddressError(array $error)
    {
        \reset($error);

        return (string) \key($error);
    }

    /**
     * @param string $responseBody
     *
     * @return int
     */
    private function getErrorCode($responseBody)
    {
        $body = json_decode($responseBody, true);
        if (!\is_array($body)) {
            return ErrorCodes::COMMUNICATION_FAILURE;
        }

        $errorTypeString = isset($body['details'][0]['issue']) ? $body['details'][0]['issue'] : '';

        if ($body['name'] === self::UNPROCESSABLE_ENTITY) {
            if ($errorTypeString === self::PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED) {
                return ErrorCodes::PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED;
            }

            if ($errorTypeString === self::PAYMENT_SOURCE_DECLINED_BY_PROCESSOR) {
                return ErrorCodes::PAYMENT_SOURCE_DECLINED_BY_PROCESSOR;
            }
        }

        if ($errorTypeString === 'INSTRUMENT_DECLINED') {
            return ErrorCodes::INSTRUMENT_DECLINED;
        }

        if ($errorTypeString === 'TRANSACTION_REFUSED') {
            return ErrorCodes::TRANSACTION_REFUSED;
        }

        return ErrorCodes::COMMUNICATION_FAILURE;
    }

    /**
     * @param string $awaitedStatus
     *
     * @return DeterminedStatus
     */
    private function determineStatus(Order $paypalOrder, $awaitedStatus = PaymentStatusV2::ORDER_CAPTURE_COMPLETED)
    {
        $successStatusDetermined = false;
        $paymentFailed = false;

        switch ($paypalOrder->getIntent()) {
            case PaymentIntentV2::AUTHORIZE:
                $authorization = $this->orderPropertyHelper->getAuthorization($paypalOrder);
                if (!$authorization instanceof Authorization) {
                    break;
                }

                $successStatusDetermined = $authorization->getStatus() === PaymentStatusV2::ORDER_AUTHORIZATION_CREATED;
                $paymentFailed = \in_array($authorization->getStatus(), [
                    PaymentStatusV2::ORDER_AUTHORIZATION_DENIED,
                    PaymentStatusV2::ORDER_AUTHORIZATION_PARTIALLY_CREATED,
                    PaymentStatusV2::ORDER_AUTHORIZATION_VOIDED,
                    PaymentStatusV2::ORDER_AUTHORIZATION_EXPIRED,
                ]);
                break;
            case PaymentIntentV2::CAPTURE:
                $capture = $this->orderPropertyHelper->getFirstCapture($paypalOrder);
                if (!$capture instanceof Capture) {
                    break;
                }

                $successStatusDetermined = \in_array($capture->getStatus(), [$awaitedStatus, PaymentStatusV2::ORDER_CAPTURE_COMPLETED]);
                $paymentFailed = \in_array($capture->getStatus(), [
                    PaymentStatusV2::ORDER_CAPTURE_DECLINED,
                    PaymentStatusV2::ORDER_CAPTURE_FAILED,
                ]);
                break;
        }

        return new DeterminedStatus($successStatusDetermined, $paymentFailed);
    }
}
