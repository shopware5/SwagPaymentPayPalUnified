<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Controllers\Frontend;

use RuntimeException;
use Shopware\Components\HttpClient\RequestException;
use Shopware\Models\Order\Status;
use Shopware_Components_Config;
use Shopware_Controllers_Frontend_Payment;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacade;
use SwagPaymentPayPalUnified\Components\Services\DispatchValidation;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderAddInvoiceIdPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use UnexpectedValueException;

class AbstractPaypalPaymentController extends Shopware_Controllers_Frontend_Payment
{
    const MAXIMUM_RETRIES = 32;
    const TIMEOUT = \CURLOPT_TIMEOUT * 2;
    const SLEEP = 1;
    const PAYMENT_SOURCE_GETTER_LIST = [
        PaymentType::PAYPAL_PAY_UPON_INVOICE_V2 => 'getPayUponInvoice',
        PaymentType::APM_BANCONTACT => 'getBancontact',
        PaymentType::APM_BLIK => 'getBlik',
        PaymentType::APM_GIROPAY => 'getGiropay',
        PaymentType::APM_IDEAL => 'getIdeal',
        PaymentType::APM_MULTIBANCO => 'getMultibanco',
        PaymentType::APM_MYBANK => 'getMybank',
        PaymentType::APM_OXXO => 'getOxxo',
        PaymentType::APM_P24 => 'getP24',
        PaymentType::APM_SOFORT => 'getSofort',
        PaymentType::APM_TRUSTLY => 'getTrustly',
    ];

    const UNPROCESSABLE_ENTITY = 'UNPROCESSABLE_ENTITY';
    const PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED = 'PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED';
    const PAYMENT_SOURCE_DECLINED_BY_PROCESSOR = 'PAYMENT_SOURCE_DECLINED_BY_PROCESSOR';

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
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode($this->getErrorCode($exception->getBody()))
                ->setException($exception, 'create PayPal order');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return null;
        } catch (\Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception, 'create PayPal order');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return null;
        }

        return $payPalOrder;
    }

    /**
     * @param string       $payPalOrderId
     * @param array<Patch> $patches
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
        } catch (\Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception, 'update PayPal order');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return false;
        }

        return true;
    }

    /**
     * @param string     $payPalOrderId
     * @param Order|null $payPalOrder
     *
     * @return Order|null
     */
    protected function captureOrAuthorizeOrder($payPalOrderId, $payPalOrder = null)
    {
        if ($payPalOrder instanceof Order && strtolower($payPalOrder->getStatus()) === PaymentStatus::PAYMENT_COMPLETED) {
            return $payPalOrder;
        }

        $capturedPayPalOrder = null;
        try {
            if ($this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_INTENT) === PaymentIntentV2::CAPTURE) {
                $this->logger->debug(sprintf('%s CAPTURE PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));

                $capturedPayPalOrder = $this->orderResource->capture($payPalOrderId, false);

                $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY CAPTURED', __METHOD__));
            } elseif ($this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_INTENT) === PaymentIntentV2::AUTHORIZE) {
                $this->logger->debug(sprintf('%s AUTHORIZE PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));

                $capturedPayPalOrder = $this->orderResource->authorize($payPalOrderId, false);

                $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY AUTHORIZED', __METHOD__));
            }
        } catch (RequestException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                ->setException($exception, 'capture/authorize PayPal order');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return null;
        } catch (\Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception, 'capture/authorize PayPal order');

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return null;
        }

        return $capturedPayPalOrder;
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
        } catch (\Exception $exception) {
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
    protected function getPaymentType(Order $order)
    {
        if ($this->request->getParam('sepaCheckout', false)) {
            return PaymentType::PAYPAL_SEPA;
        }

        if ($this->request->getParam('acdcCheckout', false)) {
            return PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD;
        }

        if ($this->request->getParam('spbCheckout', false)) {
            return PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2;
        }

        if ($this->request->getParam('inContextCheckout', false)) {
            return PaymentType::PAYPAL_CLASSIC_V2;
        }

        $paymentSource = $order->getPaymentSource();

        if (!$paymentSource instanceof PaymentSource) {
            return PaymentType::PAYPAL_CLASSIC_V2;
        }

        foreach (self::PAYMENT_SOURCE_GETTER_LIST as $paymentType => $getter) {
            if ($paymentSource->$getter() !== null) {
                return $paymentType;
            }
        }

        throw new UnexpectedValueException('Payment type not found');
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
     * @return bool
     */
    protected function getSendOrdernumber()
    {
        $sendShopwareOrderNumber = (bool) $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_SEND_ORDER_NUMBER);
        $this->logger->debug(sprintf('%s SEND SHOPWARE ORDERNUMBER: %s', __METHOD__, $sendShopwareOrderNumber ? 'TRUE' : 'FALSE'));

        return $sendShopwareOrderNumber;
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
        $legacyValidator = $this->get('paypal_unified.simple_basket_validator');

        $cart = $this->getBasket();
        $customer = $this->getUser();

        if ($cart === null || $customer === null) {
            return false;
        }

        foreach ($payPalOrder->getPurchaseUnits() as $purchaseUnit) {
            if (!$legacyValidator->validate($cart, $customer, (float) $purchaseUnit->getAmount()->getValue())) {
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
     *
     * @return bool indicating whether the payment is complete
     */
    protected function isPaymentCompleted($payPalOrderId)
    {
        $start = microtime(true);

        $this->logger->debug(sprintf('%s START POLLING AT: %f', __METHOD__, $start));

        for ($i = 0; $i <= self::MAXIMUM_RETRIES; ++$i) {
            $this->logger->debug(sprintf('%s POLLING TRY NR: %d AT: %f', __METHOD__, $i, microtime(true)));

            $paypalOrder = $this->getPayPalOrder($payPalOrderId);
            if (!$paypalOrder instanceof Order) {
                break;
            }

            if ($paypalOrder->getStatus() === PaymentStatusV2::ORDER_COMPLETED) {
                $currentTime = microtime(true);
                $elapsedTime = $currentTime - $start;
                $this->logger->debug(sprintf('%s POLLING SUCCESSFUL AFTER TRY NR: %d AT: %f AFTER: %f SECONDS', __METHOD__, $i, $currentTime, $elapsedTime));

                return true;
            }

            if ($i >= self::MAXIMUM_RETRIES || time() >= $start + self::TIMEOUT) {
                $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                    ->setCode(ErrorCodes::UNKNOWN)
                    ->setException(new RuntimeException('Maximum retries exceeded.'), '');

                $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

                break;
            }

            if ($paypalOrder->getStatus() === PaymentStatusV2::ORDER_AUTHORIZATION_DENIED) {
                $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                    ->setCode(ErrorCodes::UNKNOWN)
                    ->setException(new RuntimeException('Order has not been authorised.'), '');

                $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

                break;
            }

            sleep(self::SLEEP);
        }

        $currentTime = microtime(true);
        $elapsedTime = $currentTime - $start;
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
}
