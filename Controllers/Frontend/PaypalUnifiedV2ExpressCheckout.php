<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacadeInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrderBuilderService;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderAddInvoiceIdPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderPurchaseUnitPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;

/**
 * @phpstan-import-type CheckoutBasketArray from \Shopware_Controllers_Frontend_Checkout
 */
class Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout extends Shopware_Controllers_Frontend_Payment
{
    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var PaymentControllerHelper
     */
    private $paymentControllerHelper;

    /**
     * @var RedirectDataBuilderFactory
     */
    private $redirectDataBuilderFactory;

    /**
     * @var PayPalOrderBuilderService
     */
    private $payPalOrderBuilderService;

    /**
     * @var OrderDataService
     */
    private $orderDataService;

    /**
     * @var SettingsService
     */
    private $settingsService;

    /**
     * @var PayPalOrderParameterFacadeInterface
     */
    private $payPalOrderParameterFacade;

    public function preDispatch()
    {
        $this->orderResource = $this->get('paypal_unified.v2.order_resource');
        $this->paymentControllerHelper = $this->get('paypal_unified.payment_controller_helper');
        $this->redirectDataBuilderFactory = $this->get('paypal_unified.redirect_data_builder_factory');
        $this->payPalOrderBuilderService = $this->get('paypal_unified.paypal_order_builder_service');
        $this->orderDataService = $this->get('paypal_unified.order_data_service');
        $this->settingsService = $this->get('paypal_unified.settings_service');
        $this->payPalOrderParameterFacade = $this->get('paypal_unified.paypal_order_parameter_facade');
    }

    public function expressCheckoutFinishAction()
    {
        $payPalOrderId = $this->request->getParam('orderId');

        if (!$payPalOrderId) {
            throw new \RuntimeException('No order ID given.');
        }

        /** @phpstan-var CheckoutBasketArray $basketData */
        $basketData = $this->getBasket() ?: [];
        $userData = $this->getUser() ?: [];
        $sendShopwareOrderNumber = (bool) $this->settingsService->get('send_order_number');

        $shopwareOrderData = new ShopwareOrderData($userData, $basketData);
        $payPalOrderParameter = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_EXPRESS_V2, $shopwareOrderData);

        $payPalOrderData = $this->payPalOrderBuilderService->getOrder($payPalOrderParameter);

        $purchaseUnitPatch = new OrderPurchaseUnitPatch();
        $purchaseUnitPatch->setPath(OrderPurchaseUnitPatch::PATH);
        $purchaseUnitPatch->setOp(Patch::OPERATION_REPLACE);
        $purchaseUnitPatch->setValue(\json_decode((string) \json_encode($payPalOrderData->getPurchaseUnits()[0]), true));

        $patchSet = [$purchaseUnitPatch];

        if ($sendShopwareOrderNumber) {
            $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId);

            $orderNumberPrefix = $this->settingsService->get('order_number_prefix');

            $invoiceIdPatch = new OrderAddInvoiceIdPatch();
            $invoiceIdPatch->setOp(Patch::OPERATION_ADD);
            $invoiceIdPatch->setValue(sprintf('%s%s', $orderNumberPrefix, $shopwareOrderNumber));
            $invoiceIdPatch->setPath(OrderAddInvoiceIdPatch::PATH);

            $patchSet[] = $invoiceIdPatch;
        }

        try {
            $this->orderResource->update($patchSet, $payPalOrderId, PartnerAttributionId::PAYPAL_EXPRESS_CHECKOUT);
        } catch (RequestException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                ->setException($exception);

            $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

            return;
        }

        try {
            $this->orderResource->capture($payPalOrderId, PartnerAttributionId::PAYPAL_EXPRESS_CHECKOUT, false);
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
            $this->createShopwareOrder($payPalOrderId);
        }

        $this->redirect([
            'module' => 'frontend',
            'controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $payPalOrderId,
        ]);
    }

    /**
     * @param string $payPalOrderId
     *
     * @return string
     */
    private function createShopwareOrder($payPalOrderId)
    {
        $orderNumber = (string) $this->saveOrder($payPalOrderId, $payPalOrderId, PaymentStatus::PAYMENT_STATUS_OPEN);

        $this->orderDataService->applyPaymentTypeAttribute($orderNumber, PaymentType::PAYPAL_EXPRESS_V2);

        return $orderNumber;
    }
}
