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
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\ExpressCheckout\CustomerService;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\ErrorResponse;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class Shopware_Controllers_Widgets_PaypalUnifiedExpressCheckout extends \Enlight_Controller_Action
{
    /**
     * @var PaymentResource
     */
    private $paymentResource;

    /**
     * Initialize payment resource
     */
    public function preDispatch()
    {
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
            $productNumber = $this->Request()->getParam('productNumber');
            $productQuantity = $this->Request()->getParam('productQuantity');
            $basket->sAddArticle($productNumber, $productQuantity);
        }

        //By using the basket module we do not have to deal with any view assignments
        //as seen in the PayPalUnified controller.
        $basketData = $basket->sGetBasket();

        $profile = $this->get('paypal_unified.web_profile_service')->getWebProfile();

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
        $requestParams->setWebProfile($profile);

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
