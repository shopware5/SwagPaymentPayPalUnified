<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\PaymentAddressService;
use SwagPaymentPayPalUnified\Components\Services\PaymentTokenExtractor;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PayerInfoPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAddressPatch;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class Shopware_Controllers_Widgets_PaypalUnifiedSmartPaymentButtons extends Shopware_Controllers_Frontend_Payment
{
    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var \Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @var PaymentResource
     */
    private $paymentResource;

    public function preDispatch()
    {
        $this->paymentResource = $this->get('paypal_unified.payment_resource');
        $this->dependencyProvider = $this->get('paypal_unified.dependency_provider');
        $this->shopwareConfig = $this->get('config');
    }

    public function createPaymentAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer();
        $view = $this->View();
        $view->setTemplate();

        $orderData = $this->dependencyProvider->getSession()->get('sOrderVariables');
        if ($orderData === null) {
            $this->handleError(ErrorCodes::NO_ORDER_TO_PROCESS);

            return;
        }

        if ($this->noDispatchForOrder()) {
            $this->handleError(ErrorCodes::NO_DISPATCH_FOR_ORDER);

            return;
        }

        $basketData = $orderData['sBasket'];
        $userData = $orderData['sUserData'];

        $requestParams = new PaymentBuilderParameters();
        $requestParams->setBasketData($basketData);
        $requestParams->setUserData($userData);
        $requestParams->setPaymentToken($this->dependencyProvider->createPaymentToken());

        $basketUniqueId = null;
        // Prepare the new basket signature feature, announced in SW 5.3.0
        if (version_compare($this->shopwareConfig->offsetGet('version'), '5.3.0', '>=')) {
            $basketUniqueId = $this->persistBasket();
            $requestParams->setBasketUniqueId($basketUniqueId);
        }

        $requestParams->setPaymentType(PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS);
        $this->get('paypal_unified.client_service')->setPartnerAttributionId(PartnerAttributionId::PAYPAL_SMART_PAYMENT_BUTTONS);
        $params = $this->get('paypal_unified.payment_builder_service')->getPayment($requestParams);

        try {
            $response = $this->paymentResource->create($params);
            $responseStruct = Payment::fromArray($response);
        } catch (RequestException $requestEx) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $requestEx);

            return;
        } catch (Exception $exception) {
            $this->handleError(ErrorCodes::UNKNOWN, $exception);

            return;
        }

        /** @var PaymentAddressService $addressService */
        $addressService = $this->get('paypal_unified.payment_address_service');
        $addressPatch = new PaymentAddressPatch($addressService->getShippingAddress($userData));
        $payerInfoPatch = new PayerInfoPatch($addressService->getPayerInfo($userData));

        try {
            $this->paymentResource->patch($responseStruct->getId(), [$addressPatch, $payerInfoPatch]);
        } catch (RequestException $requestEx) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $requestEx);

            return;
        } catch (Exception $exception) {
            $this->handleError(ErrorCodes::UNKNOWN, $exception);

            return;
        }

        $this->view->assign('token', PaymentTokenExtractor::extract($responseStruct));
        $this->view->assign('basketId', $basketUniqueId);
    }

    /**
     * This method handles the redirection to the shippingPayment action if an
     * error has occurred during the payment process.
     *
     * @param int $code
     *
     * @see ErrorCodes
     */
    private function handleError($code, Exception $exception = null)
    {
        /** @var string $message */
        $message = null;
        $name = null;

        if ($exception) {
            /** @var ExceptionHandlerServiceInterface $exceptionHandler */
            $exceptionHandler = $this->get('paypal_unified.exception_handler_service');
            $error = $exceptionHandler->handle($exception, 'process smart-payment-buttons-checkout');

            $settingsService = $this->get('paypal_unified.settings_service');
            if ($settingsService->hasSettings() && $settingsService->get('display_errors')) {
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

        $this->View()->assign('errorUrl', $this->Front()->Router()->assemble($redirectData));
    }

    /**
     * @return bool
     */
    private function noDispatchForOrder()
    {
        $session = $this->dependencyProvider->getSession();

        return !empty($this->shopwareConfig->get('premiumShippingNoOrder')) && (empty($session->get('sDispatch')) || empty($session->get('sCountry')));
    }
}
