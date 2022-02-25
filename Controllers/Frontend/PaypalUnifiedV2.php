<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common\Link;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderAddInvoiceIdPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;

class Shopware_Controllers_Frontend_PaypalUnifiedV2 extends AbstractPaypalPaymentController
{
    public function preDispatch()
    {
        parent::preDispatch();

        $this->logger->debug(__METHOD__);

        if ($this->Request()->isXmlHttpRequest()) {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
            $this->Front()->Plugins()->Json()->setRenderer();
            $this->View()->setTemplate();

            $this->logger->debug(sprintf('%s %s', __METHOD__, 'IS XHR REQUEST'));
        }
    }

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
        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_CLASSIC_V2, $shopwareOrderData);

        try {
            $this->logger->debug(sprintf('%s BEFORE CREATE PAYPAL ORDER', __METHOD__));

            $payPalOrderData = $this->orderFactory->createOrder($orderParams);
            $payPalOrder = $this->orderResource->create($payPalOrderData, $orderParams->getPaymentType(), PartnerAttributionId::PAYPAL_ALL_V2, false);

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFUL CREATED: ID: %d', __METHOD__, $payPalOrder->getId()));
        } catch (RequestException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        } catch (\Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        if ($this->Request()->isXmlHttpRequest()) {
            $this->logger->debug(sprintf('%s IS XHR REQUEST', __METHOD__));

            $this->view->assign('token', $payPalOrder->getId());
            $this->view->assign('basketId', $orderParams->getBasketUniqueId());

            return;
        }

        $url = null;
        foreach ($payPalOrder->getLinks() as $link) {
            if ($link->getRel() === Link::RELATION_APPROVE) {
                $url = $link->getHref();
            }
        }

        if ($url === null) {
            $this->logger->debug(sprintf('%s NO URL FOUND', __METHOD__));

            throw new \RuntimeException('No link for redirect found');
        }

        $this->logger->debug(sprintf('%s REDIRECT TO: %s', __METHOD__, $url));

        $this->redirect($url);
    }

    public function returnAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $request = $this->Request();
        $payPalOrderId = $request->getParam('token');

        try {
            $this->logger->debug(sprintf('%s GET PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));

            $payPalOrder = $this->orderResource->get($payPalOrderId);

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY LOADED', __METHOD__));
        } catch (RequestException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        } catch (\Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        if ($this->dispatchValidator->isInvalid()) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::BASKET_VALIDATION_ERROR);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        $sendShopwareOrderNumber = $this->getSendOrdernumber();

        $this->logger->debug(sprintf('%s SEND SHOPWARE ORDERNUMBER: %s', __METHOD__, $sendShopwareOrderNumber ? 'TRUE' : 'FALSE'));

        if ($sendShopwareOrderNumber) {
            $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, $this->getPaymentType($payPalOrder));

            $orderNumberPrefix = $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_ORDER_NUMBER_PREFIX);

            $invoiceIdPatch = new OrderAddInvoiceIdPatch();
            $invoiceIdPatch->setOp(Patch::OPERATION_ADD);
            $invoiceIdPatch->setValue(sprintf('%s%s', $orderNumberPrefix, $shopwareOrderNumber));
            $invoiceIdPatch->setPath(OrderAddInvoiceIdPatch::PATH);

            try {
                $this->logger->debug(sprintf('%s UPDATE PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));

                $this->orderResource->update([$invoiceIdPatch], $payPalOrder->getId(), PaymentType::PAYPAL_CLASSIC_V2);

                $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY UPDATED', __METHOD__));
            } catch (RequestException $exception) {
                $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                    ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                    ->setException($exception);

                $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

                return;
            }
        }

        try {
            if ($this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_INTENT) === PaymentIntentV2::CAPTURE) {
                $this->logger->debug(sprintf('%s CAPTURE PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));

                $this->orderResource->capture($payPalOrder->getId(), PartnerAttributionId::PAYPAL_ALL_V2, false);

                $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY CAPTURED', __METHOD__));
            } elseif ($this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_INTENT) === PaymentIntentV2::AUTHORIZE) {
                $this->logger->debug(sprintf('%s AUTHORIZE PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));

                $this->orderResource->authorize($payPalOrder->getId(), PartnerAttributionId::PAYPAL_ALL_V2, false);

                $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY AUTHORIZED', __METHOD__));
            }
        } catch (RequestException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        } catch (\Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        if (!$sendShopwareOrderNumber) {
            $this->createShopwareOrder($payPalOrderId, $this->getPaymentType($payPalOrder));
        }

        if ($this->Request()->isXmlHttpRequest()) {
            $this->view->assign('paypalOrderId', $payPalOrderId);

            return;
        }

        $this->logger->debug(sprintf('%s REDIRECT TO checkout/finish', __METHOD__));

        $this->redirect([
            'module' => 'frontend',
            'controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $payPalOrderId,
        ]);
    }
}
