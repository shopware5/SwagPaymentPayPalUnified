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
use SwagPaymentPayPalUnified\Components\ExpressCheckout\CustomerService;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\ErrorResponse;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\GenericErrorResponse;
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
     * Initialize payment resource
     */
    public function preDispatch()
    {
        $this->logger = $this->get('paypal_unified.logger_service');
        $this->paymentResource = $this->get('paypal_unified.payment_resource');
    }

    public function createPaymentAction()
    {
        /** @var sBasket $basket */
        $basket = $this->get('paypal_unified.dependency_provider')->getModule('basket');

        //If the paypal express button on the detail page was clicked, the addProduct equals true.
        //That means, that we have to add it manually to the basket.
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
            $admin = $this->get('paypal_unified.dependency_provider')->getModule('admin');
            $countries = $admin->sGetCountryList();
            $admin->sGetPremiumShippingcosts(reset($countries));
        }

        //By using the basket module we do not have to deal with any view assignments
        //as seen in the PayPalUnified controller.
        $basketData = $basket->sGetBasket();

        $webProfileId = $this->get('paypal_unified.settings_service')->get('web_profile_id_ec');

        $userData = [
            'additional' => [
                'show_net' => !(bool) $this->get('session')->get('sOutputNet'),
            ],
        ];

        /** @var \Shopware\Models\Shop\DetachedShop $shop */
        $shop = $this->get('paypal_unified.dependency_provider')->getShop();
        $currency = $shop->getCurrency()->getCurrency();

        $requestParams = new PaymentBuilderParameters();
        $requestParams->setBasketData($basketData);
        $requestParams->setUserData($userData);
        $requestParams->setWebProfileId($webProfileId);
        $requestParams->setPaymentType(PaymentType::PAYPAL_EXPRESS);

        try {
            /** @var Payment $params */
            $params = $this->get('paypal_unified.express_checkout.payment_builder_service')->getPayment($requestParams, $currency);

            $response = $this->paymentResource->create($params);
            $responseStruct = Payment::fromArray($response);
        } catch (RequestException $requestEx) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $requestEx);

            return;
        } catch (\Exception $exception) {
            $this->handleError(ErrorCodes::UNKNOWN);

            return;
        }

        $useInContext = $this->Request()->getParam('useInContext', false);
        if ($useInContext) {
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
            $payment = $this->paymentResource->get($paymentId);

            $paymentStruct = Payment::fromArray($payment);
        } catch (RequestException $requestEx) {
            $this->handleError(ErrorCodes::COMMUNICATION_FAILURE, $requestEx);

            return;
        } catch (\Exception $exception) {
            $this->handleError(ErrorCodes::UNKNOWN);

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
            $this->logger->error('Received an error during express-checkout process', ['payload' => $exception->getBody()]);

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
}
