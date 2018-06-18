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
use SwagPaymentPayPalUnified\Components\ExpressCheckout\CustomerService;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class Shopware_Controllers_Widgets_PaypalUnifiedExpressCheckout extends \Enlight_Controller_Action
{
    /**
     * @var PaymentResource
     */
    private $paymentResource;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var ClientService
     */
    private $client;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    public function preDispatch()
    {
        $this->paymentResource = $this->get('paypal_unified.payment_resource');
        $this->client = $this->get('paypal_unified.client_service');
        $this->logger = $this->get('paypal_unified.logger_service');
        $this->dependencyProvider = $this->get('paypal_unified.dependency_provider');
        $this->settingsService = $this->get('paypal_unified.settings_service');
    }

    public function createPaymentAction()
    {
        /** @var sBasket $basket */
        $basket = $this->dependencyProvider->getModule('basket');

        //If the PayPal express button on the detail page was clicked, the addProduct equals true.
        //That means, that it has to be added manually to the basket.
        $addProductToBasket = $this->Request()->getParam('addProduct', false);
        if ($addProductToBasket) {
            // delete the cart, to make sure that only the selected product is transferred to PayPal
            $basket->sDeleteBasket();
            $productNumber = $this->Request()->getParam('productNumber');
            $productQuantity = $this->Request()->getParam('productQuantity');
            $basket->sAddArticle($productNumber, $productQuantity);

            // add potential discounts or surcharges to prevent an amount mismatch
            // on patching the new amount after the confirmation.
            // only necessary if the customer directly checks out from product detail page
            /** @var sAdmin $admin */
            $admin = $this->dependencyProvider->getModule('admin');
            $countries = $admin->sGetCountryList();
            $admin->sGetPremiumShippingcosts(reset($countries));
        }

        // By using the basket module it is not necessary to deal with any view assignments
        // as seen in the PayPalUnified controller.
        $basketData = $basket->sGetBasket();

        $webProfileId = $this->settingsService->get('web_profile_id', SettingsTable::EXPRESS_CHECKOUT);

        $userData = [
            'additional' => [
                'show_net' => !(bool) $this->get('session')->get('sOutputNet'),
            ],
        ];

        /** @var \Shopware\Models\Shop\DetachedShop $shop */
        $shop = $this->dependencyProvider->getShop();
        $currency = $shop->getCurrency()->getCurrency();

        $requestParams = new PaymentBuilderParameters();
        $requestParams->setBasketData($basketData);
        $requestParams->setUserData($userData);
        $requestParams->setWebProfileId($webProfileId);
        $requestParams->setPaymentType(PaymentType::PAYPAL_EXPRESS);

        $this->client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_EXPRESS_CHECKOUT);

        try {
            /** @var Payment $params */
            $params = $this->get('paypal_unified.express_checkout.payment_builder_service')->getPayment($requestParams, $currency);

            $response = $this->paymentResource->create($params);
            $responseStruct = Payment::fromArray($response);
        } catch (RequestException $requestEx) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $requestEx);

            return;
        } catch (Exception $exception) {
            $this->handleError(ErrorCodes::UNKNOWN, $exception);

            return;
        }

        if ($this->Request()->getParam('useInContext', false)) {
            $this->Front()->Plugins()->Json()->setRenderer();

            $this->View()->assign('paymentId', $responseStruct->getId());

            return;
        }

        $this->redirect($responseStruct->getLinks()[1]->getHref());
    }

    public function expressCheckoutReturnAction()
    {
        $request = $this->Request();
        $paymentId = $request->getParam('paymentId');
        $payerId = $request->getParam('PayerID');
        $basketId = $request->getParam('basketId');

        try {
            $this->client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_EXPRESS_CHECKOUT);
            $payment = $this->paymentResource->get($paymentId);

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

        if ($exception && $this->settingsService->hasSettings() && $this->settingsService->get('display_errors')) {
            /** @var ExceptionHandlerServiceInterface $exceptionHandler */
            $exceptionHandler = $this->get('paypal_unified.exception_handler_service');

            $error = $exceptionHandler->handle($exception, 'process express-checkout');
            $message = $error->getMessage();
            $name = $error->getName();
        }

        $this->redirect([
            'controller' => 'checkout',
            'action' => 'shippingPayment',
            'expressCheckout' => true,
            'paypal_unified_error_code' => $code,
            'paypal_unified_error_name' => $name,
            'paypal_unified_error_message' => $message,
        ]);
    }
}
