<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderPurchaseUnitPatch;

/**
 * @phpstan-import-type CheckoutBasketArray from \Shopware_Controllers_Frontend_Checkout
 */
class Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout extends AbstractPaypalPaymentController
{
    /**
     * @return void
     */
    public function expressCheckoutFinishAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $payPalOrderId = $this->request->getParam('orderId');

        if (!\is_string($payPalOrderId)) {
            $this->logger->debug(sprintf('%s NO ORDER ID GIVEN', __METHOD__));

            throw new \RuntimeException('No order ID given.');
        }

        /** @phpstan-var CheckoutBasketArray $basketData */
        $basketData = $this->getBasket() ?: [];
        $userData = $this->getUser() ?: [];

        $shopwareOrderData = new ShopwareOrderData($userData, $basketData);
        $payPalOrderParameter = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2, $shopwareOrderData);

        $payPalOrderData = $this->orderFactory->createOrder($payPalOrderParameter);

        $purchaseUnitPatch = new OrderPurchaseUnitPatch();
        $purchaseUnitPatch->setPath(OrderPurchaseUnitPatch::PATH);
        $purchaseUnitPatch->setOp(Patch::OPERATION_REPLACE);
        $purchaseUnitPatch->setValue(json_decode((string) json_encode($payPalOrderData->getPurchaseUnits()[0]), true));

        $patchSet = [$purchaseUnitPatch];

        $sendShopwareOrderNumber = $this->getSendOrdernumber();
        if ($sendShopwareOrderNumber) {
            $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, $payPalOrderParameter->getPaymentType());

            $patchSet[] = $this->createInvoiceIdPatch($shopwareOrderNumber);
        }

        if (!$this->updatePayPalOrder($payPalOrderId, $patchSet)) {
            return;
        }

        if (!$this->captureOrAuthorizeOrder($payPalOrderId)) {
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
