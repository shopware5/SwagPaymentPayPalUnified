<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InvalidBillingAddressException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InvalidShippingAddressException;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use Symfony\Component\HttpFoundation\Response;

class Shopware_Controllers_Widgets_PaypalUnifiedV2SmartPaymentButtons extends AbstractPaypalPaymentController
{
    public function preDispatch()
    {
        parent::preDispatch();

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer();
        $this->view->setTemplate();
    }

    /**
     * @return void
     */
    public function createOrderAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $session = $this->dependencyProvider->getSession();
        $shopwareSessionOrderData = $session->get('sOrderVariables');
        $this->handleComment();
        $this->handleNewsletter();

        if ($shopwareSessionOrderData === null) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::NO_ORDER_TO_PROCESS);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        if ($this->dispatchValidator->isInvalid()) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::NO_DISPATCH_FOR_ORDER);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $shopwareOrderData = new ShopwareOrderData($shopwareSessionOrderData['sUserData'], $shopwareSessionOrderData['sBasket']);
        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2, $shopwareOrderData);

        try {
            $payPalOrder = $this->createPayPalOrder($orderParams);
        } catch (InvalidBillingAddressException $invalidBillingAddressException) {
            $this->response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
            $this->view->assign('redirectTo', $this->getInvalidAddressUrl(['invalidBillingAddress' => true]));

            return;
        } catch (InvalidShippingAddressException $invalidShippingAddressException) {
            $this->response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
            $this->view->assign('redirectTo', $this->getInvalidAddressUrl(['invalidShippingAddress' => true]));

            return;
        }

        if (!$payPalOrder instanceof Order) {
            return;
        }

        $this->view->assign('paypalOrderId', $payPalOrder->getId());
        $this->view->assign('basketId', $orderParams->getBasketUniqueId());
    }
}
