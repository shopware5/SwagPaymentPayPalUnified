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
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderAddInvoiceIdPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderPurchaseUnitPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;

/**
 * @phpstan-import-type CheckoutBasketArray from \Shopware_Controllers_Frontend_Checkout
 */
class Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout extends AbstractPaypalPaymentController
{
    public function expressCheckoutFinishAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $payPalOrderId = $this->request->getParam('orderId');

        if (!$payPalOrderId) {
            $this->logger->debug(sprintf('%s NO ORDER ID GIVEN', __METHOD__));

            throw new \RuntimeException('No order ID given.');
        }

        /** @phpstan-var CheckoutBasketArray $basketData */
        $basketData = $this->getBasket() ?: [];
        $userData = $this->getUser() ?: [];
        $sendShopwareOrderNumber = (bool) $this->settingsService->get(SettingsServiceInterface::SETTING_SEND_ORDER_NUMBER);

        $shopwareOrderData = new ShopwareOrderData($userData, $basketData);
        $payPalOrderParameter = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2, $shopwareOrderData);

        $payPalOrderData = $this->orderFactory->createOrder($payPalOrderParameter);

        $purchaseUnitPatch = new OrderPurchaseUnitPatch();
        $purchaseUnitPatch->setPath(OrderPurchaseUnitPatch::PATH);
        $purchaseUnitPatch->setOp(Patch::OPERATION_REPLACE);
        $purchaseUnitPatch->setValue(json_decode((string) json_encode($payPalOrderData->getPurchaseUnits()[0]), true));

        $patchSet = [$purchaseUnitPatch];

        $this->logger->debug(sprintf('%s SEND SHOPWARE ORDERNUMBER: %s', __METHOD__, $sendShopwareOrderNumber ? 'TRUE' : 'FALSE'));

        if ($sendShopwareOrderNumber) {
            $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, $payPalOrderParameter->getPaymentType());

            $orderNumberPrefix = $this->settingsService->get(SettingsServiceInterface::SETTING_ORDER_NUMBER_PREFIX);

            $invoiceIdPatch = new OrderAddInvoiceIdPatch();
            $invoiceIdPatch->setOp(Patch::OPERATION_ADD);
            $invoiceIdPatch->setValue(sprintf('%s%s', $orderNumberPrefix, $shopwareOrderNumber));
            $invoiceIdPatch->setPath(OrderAddInvoiceIdPatch::PATH);

            $patchSet[] = $invoiceIdPatch;
        }

        try {
            $this->logger->debug(sprintf('%s UPDATE PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));

            $this->orderResource->update($patchSet, $payPalOrderId, PartnerAttributionId::PAYPAL_ALL_V2);

            $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY UPDATED', __METHOD__));
        } catch (RequestException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        try {
            if ($this->settingsService->get(SettingsServiceInterface::SETTING_INTENT) === PaymentIntentV2::CAPTURE) {
                $this->logger->debug(sprintf('%s CAPTURE PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));

                $this->orderResource->capture($payPalOrderId, PartnerAttributionId::PAYPAL_ALL_V2, false);

                $this->logger->debug(sprintf('%s PAYPAL ORDER SUCCESSFULLY CAPTURED', __METHOD__));
            } elseif ($this->settingsService->get(SettingsServiceInterface::SETTING_INTENT) === PaymentIntentV2::AUTHORIZE) {
                $this->logger->debug(sprintf('%s AUTHORIZE PAYPAL ORDER WITH ID: %s', __METHOD__, $payPalOrderId));

                $this->orderResource->authorize($payPalOrderId, PartnerAttributionId::PAYPAL_ALL_V2, false);

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

        $this->logger->debug(sprintf('%s SEND SHOPWARE ORDERNUMBER: %s', __METHOD__, $sendShopwareOrderNumber ? 'TRUE' : 'FALSE'));

        if (!$sendShopwareOrderNumber) {
            $this->createShopwareOrder($payPalOrderId, $payPalOrderParameter->getPaymentType());
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
