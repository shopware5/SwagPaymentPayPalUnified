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
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InstrumentDeclinedException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\NoOrderToProceedException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\PayerActionRequiredException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\RequireRestartException;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common\Link;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

class Shopware_Controllers_Frontend_PaypalUnifiedApm extends AbstractPaypalPaymentController
{
    /**
     * @return void
     */
    public function indexAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $session = $this->dependencyProvider->getSession();

        $shopwareSessionOrderData = $session->get('sOrderVariables');

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

        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(
            $this->getPaymentTypeByName($shopwareOrderData->getShopwareUserData()['additional']['payment']['name']),
            $shopwareOrderData
        );

        $payPalOrder = $this->createPayPalOrder($orderParams);
        if (!$payPalOrder instanceof Order) {
            return;
        }

        $url = $this->getUrl($payPalOrder, Link::RELATION_PAYER_ACTION_REQUIRED);

        $this->logger->debug(sprintf('%s REDIRECT TO: %s', __METHOD__, $url));

        $this->redirect($url);
    }

    /**
     * This action is called when the user is being redirected back from PayPal after a successful payment process.
     * The order is saved here in the system and handle the data exchange with PayPal.
     * Required parameters:
     *  (string) paymentId
     *  (string) PayerID
     *
     * @return void
     */
    public function returnAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $payPalOrderId = $this->Request()->getParam('token');

        $payPalOrder = $this->getPayPalOrder($payPalOrderId);
        if (!$payPalOrder instanceof Order) {
            return;
        }

        if (!$this->isCartValid($payPalOrder)) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::BASKET_VALIDATION_ERROR);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $paymentType = $this->getPaymentType($payPalOrder);
        $orderNumberResult = $this->patchOrderNumber($payPalOrder);
        if (!$orderNumberResult->getSuccess()) {
            $this->orderNumberService->restoreOrdernumberToPool($orderNumberResult->getShopwareOrderNumber());

            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        try {
            $this->captureOrAuthorizeOrder($payPalOrder);
        } catch (RequireRestartException $requireRestartException) {
            $this->logger->debug(sprintf('%s REQUIRES A RESTART', __METHOD__));

            $this->orderNumberService->releaseOrderNumber();

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'PaypalUnifiedApm',
                'action' => 'return',
                'token' => $payPalOrderId,
            ]);

            return;
        } catch (PayerActionRequiredException $payerActionRequiredException) {
            $this->logger->debug(sprintf('%s PAYER_ACTION_REQUIRED', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'confirm',
                'payerActionRequired' => true,
            ]);

            return;
        } catch (InstrumentDeclinedException $instrumentDeclinedException) {
            $this->logger->debug(sprintf('%s INSTRUMENT_DECLINED', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'confirm',
                'payerInstrumentDeclined' => true,
            ]);

            return;
        } catch (NoOrderToProceedException $noOrderToProceedException) {
            $this->orderNumberService->restoreOrdernumberToPool($orderNumberResult->getShopwareOrderNumber());

            return;
        }

        if ($this->Request()->isXmlHttpRequest()) {
            $this->view->assign('paypalOrderId', $payPalOrderId);

            $this->logger->debug(sprintf('%s IS XHR REQUEST', __METHOD__));

            return;
        }

        if ($this->isPaymentCompleted($payPalOrderId)) {
            $payPalOrder = $this->getPayPalOrder($payPalOrderId);
            if (!$payPalOrder instanceof Order) {
                return;
            }

            $this->createShopwareOrder($payPalOrderId, $paymentType);

            $this->setTransactionId($orderNumberResult->getShopwareOrderNumber(), $payPalOrder);

            $this->updatePaymentStatus($payPalOrder->getIntent(), $this->getOrderId($orderNumberResult->getShopwareOrderNumber()));

            $this->logger->debug(sprintf('%s REDIRECT TO checkout/finish', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $payPalOrderId,
            ]);
        }
    }
}
