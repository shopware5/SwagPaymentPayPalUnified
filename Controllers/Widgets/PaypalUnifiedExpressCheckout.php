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
use SwagPaymentPayPalUnified\Components\ExpressCheckout\CustomerService;
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
     * initialize payment resource
     */
    public function preDispatch()
    {
        $this->paymentResource = $this->get('paypal_unified.payment_resource');
    }

    public function createPaymentAction()
    {
        $cartData = $this->Request()->getParam('cartData');

        $profile = $this->get('paypal_unified.web_profile_service')->getWebProfile();
        $basketData = json_decode($cartData, true);
        $userData = [
            'additional' => [
                'show_net' => !(bool) $this->get('session')->get('sOutputNet'),
            ],
        ];

        try {
            $params = $this->get('paypal_unified.express_checkout.payment_request_service')->getRequestParameters(
                $profile,
                $basketData,
                $userData
            );
            $response = $this->paymentResource->create($params);
            $responseStruct = Payment::fromArray($response);
        } catch (RequestException $requestEx) {
            //Communication failure
            $this->handleError(2, $requestEx);

            return;
        } catch (\Exception $exception) {
            //Unknown error
            $this->handleError(4);

            return;
        }

        $this->redirect($responseStruct->getLinks()[1]->getHref());
    }

    public function expressCheckoutReturnAction()
    {
        $request = $this->Request();
        $paymentId = $request->getParam('paymentId');
        $payerId = $request->getParam('PayerID');

        try {
            $payment = $this->paymentResource->get($paymentId);
            $paymentStruct = Payment::fromArray($payment);
        } catch (RequestException $requestEx) {
            //Communication failure
            $this->handleError(2, $requestEx);

            return;
        } catch (\Exception $exception) {
            //Unknown error
            $this->handleError(4);

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
        ]);
    }

    /**
     * This method handles the redirection to the shippingPayment action if an
     * error has occurred during the payment process.
     *
     * It takes the following parameters from the URL:
     * - code (int): The code of the error that occurred. Depending on the code,
     *               another message will be displayed in the frontend.
     *      0 = No order to be processed
     *      1 = User has canceled the process
     *      2 = Communication failure
     *      3 = System order failure
     *      Any other = Unknown error
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
