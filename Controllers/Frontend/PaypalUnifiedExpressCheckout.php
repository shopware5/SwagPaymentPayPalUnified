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
use SwagPaymentPayPalUnified\Components\Services\ExpressCheckout\CustomerService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class Shopware_Controllers_Frontend_PaypalUnifiedExpressCheckout extends Enlight_Controller_Action
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    public function preDispatch()
    {
        $this->settingsService = $this->get('paypal_unified.settings_service');
    }

    public function expressCheckoutReturnAction()
    {
        $request = $this->Request();
        $paymentId = $request->getParam('paymentId');
        $payerId = $request->getParam('PayerID');
        $basketId = $request->getParam('basketId');

        /** @var ClientService $client */
        $client = $this->get('paypal_unified.client_service');
        /** @var PaymentResource $paymentResource */
        $paymentResource = $this->get('paypal_unified.payment_resource');
        try {
            $client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_EXPRESS_CHECKOUT);
            $payment = $paymentResource->get($paymentId);

            $paymentStruct = Payment::fromArray($payment);
        } catch (RequestException $requestEx) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $requestEx);

            return;
        } catch (Exception $exception) {
            $this->handleError(ErrorCodes::UNKNOWN, $exception);

            return;
        }

        /** @var CustomerService $customerService */
        $customerService = $this->get('paypal_unified.express_checkout.customer_service');
        $customerService->createNewCustomer($paymentStruct);

        $this->redirect([
            'controller' => 'checkout',
            'action' => 'confirm',
            'expressCheckout' => true,
            'paymentId' => $paymentId,
            'payerId' => $payerId,
            'basketId' => $basketId,
        ]);
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
            $error = $exceptionHandler->handle($exception, 'process express-checkout');

            if ($this->settingsService->hasSettings() && $this->settingsService->get('display_errors')) {
                $message = $error->getMessage();
                $name = $error->getName();
            }
        }

        $redirectData = [
            'controller' => 'checkout',
            'action' => 'shippingPayment',
            'expressCheckout' => true,
            'paypal_unified_error_code' => $code,
        ];

        if ($name !== null) {
            $redirectData['paypal_unified_error_name'] = $name;
            $redirectData['paypal_unified_error_message'] = $message;
        }

        $this->redirect($redirectData);
    }
}
