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
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\PayPalOrderBuilderParameter;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrderBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common\Link;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderAddInvoiceIdPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;

class Shopware_Controllers_Frontend_PaypalUnifiedV2 extends Shopware_Controllers_Frontend_Payment
{
    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var PayPalOrderBuilderService
     */
    private $orderBuilderService;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var RedirectDataBuilderFactory
     */
    private $redirectDataBuilderFactory;

    /**
     * @var Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    public function preDispatch()
    {
        $this->dependencyProvider = $this->get('paypal_unified.dependency_provider');
        $this->orderBuilderService = $this->get('paypal_unified.paypal_order_builder_service');
        $this->orderResource = $this->get('paypal_unified.v2.order_resource');
        $this->redirectDataBuilderFactory = $this->get('paypal_unified.redirect_data_builder_factory');
        $this->shopwareConfig = $this->get('config');
        $this->settingsService = $this->get('paypal_unified.settings_service');
    }

    public function indexAction()
    {
        $session = $this->dependencyProvider->getSession();
        $orderData = $session->get('sOrderVariables');

        if ($orderData === null) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::NO_ORDER_TO_PROCESS);

            $this->handleError($redirectDataBuilder);

            return;
        }

        if ($this->getDispatchNoOrder()) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::NO_DISPATCH_FOR_ORDER);

            $this->handleError($redirectDataBuilder);

            return;
        }

        $orderParams = new PayPalOrderBuilderParameter(
            $this->prepareUserData($orderData),
            $orderData['sBasket'],
            PaymentType::PAYPAL_CLASSIC_V2,
            $this->createBasketUniqueId(),
            $this->dependencyProvider->createPaymentToken()
        );

        try {
            $orderData = $this->orderBuilderService->getOrder($orderParams);
            $response = $this->orderResource->create($orderData, PartnerAttributionId::PAYPAL_CLASSIC, false);
        } catch (RequestException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                ->setException($exception);

            $this->handleError($redirectDataBuilder);

            return;
        } catch (\Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception);

            $this->handleError($redirectDataBuilder);

            return;
        }

        $url = null;
        foreach ($response->getLinks() as $link) {
            if ($link->getRel() === Link::RELATION_APPROVE) {
                $url = $link->getHref();
            }
        }

        if ($url === null) {
            throw new \RuntimeException('No link for redirect found');
        }

        $this->redirect($url);
    }

    public function returnAction()
    {
        $request = $this->Request();
        $paymentId = $request->getParam('token');

        try {
            $order = $this->orderResource->get($paymentId);
        } catch (\Exception $exception) {
            return;
        }

        if (!$this->isCartValid($order)) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::BASKET_VALIDATION_ERROR);

            $this->handleError($redirectDataBuilder);

            return;
        }

        // TODO:: Create paymentType attribute for API_clientService PT-12464, PT-12495, PT-12496

        $sendOrdernumber = $this->getSendOrdernumber();

        if ($sendOrdernumber) {
            $orderNumber = (string) $this->saveOrder($paymentId, $paymentId, PaymentStatus::PAYMENT_STATUS_OPEN);
            $orderNumberPrefix = $this->settingsService->get('order_number_prefix');

            $invoiceIdPatch = new OrderAddInvoiceIdPatch();
            $invoiceIdPatch->setOp(Patch::OPERATION_ADD);
            $invoiceIdPatch->setValue(sprintf('%s%s', $orderNumberPrefix, $orderNumber));
            $invoiceIdPatch->setPath(OrderAddInvoiceIdPatch::PATH);

            try {
                $this->orderResource->update([$invoiceIdPatch], $order->getId(), PaymentType::PAYPAL_CLASSIC_V2);
            } catch (RequestException $exception) {
                $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                    ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                    ->setException($exception);

                $this->handleError($redirectDataBuilder);

                return;
            }
        }

        try {
            $this->orderResource->capture($order->getId(), PartnerAttributionId::PAYPAL_CLASSIC, false);
        } catch (RequestException $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                ->setException($exception);

            $this->handleError($redirectDataBuilder);

            return;
        } catch (\Exception $exception) {
            $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                ->setCode(ErrorCodes::UNKNOWN)
                ->setException($exception);

            $this->handleError($redirectDataBuilder);

            return;
        }

        if (!$sendOrdernumber) {
            $this->saveOrder($paymentId, $paymentId, PaymentStatus::PAYMENT_STATUS_OPEN);
        }

        $this->redirect([
            'module' => 'frontend',
            'controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $paymentId,
        ]);
    }

    private function handleError(RedirectDataBuilder $redirectDataBuilder)
    {
        if ($this->Request()->isXmlHttpRequest()) {
            $this->renderJson($redirectDataBuilder);

            return;
        }

        $this->redirect($redirectDataBuilder->getRedirectData());
    }

    private function renderJson(RedirectDataBuilder $redirectDataBuilder)
    {
        $this->Front()->Plugins()->Json()->setRenderer();

        $view = $this->View();
        $view->setTemplate();

        $view->assign('errorCode', $redirectDataBuilder->getCode());
        if ($redirectDataBuilder->hasException()) {
            $view->assign([
                'paypal_unified_error_name' => $redirectDataBuilder->getErrorName(),
                'paypal_unified_error_message' => $redirectDataBuilder->getErrorMessage(),
            ]);
        }
    }

    /**
     * @return bool
     */
    private function getDispatchNoOrder()
    {
        $session = $this->dependencyProvider->getSession();

        return !empty($this->shopwareConfig->get('premiumShippingNoOrder')) && (empty($session->get('sDispatch')) || empty($session->get('sCountry')));
    }

    /**
     * @param string|null $basketId
     *
     * @return bool
     */
    private function shouldUseExtendedBasketValidator($basketId = null)
    {
        if ($basketId === null) {
            return false;
        }

        if ($basketId === 'express') {
            return false;
        }

        if ($this->container->has('basket_signature_generator')) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function isCartValid(Order $order)
    {
        $basketId = $this->Request()->getParam('basketId');

        if ($this->shouldUseExtendedBasketValidator($basketId)) {
            return $this->validateBasketExtended($basketId);
        }

        return $this->validateBasketSimple($order);
    }

    /**
     * @return bool
     */
    private function getSendOrdernumber()
    {
        return $this->settingsService->get('send_order_number');
    }

    private function createBasketUniqueId()
    {
        $basketUniqueId = null;
        if ($this->container->has('basket_signature_generator')) {
            $basketUniqueId = $this->persistBasket();
        }

        return $basketUniqueId;
    }

    /**
     * @return array
     */
    private function prepareUserData(array $orderData)
    {
        $userData = $orderData['sUserData'];
        $userData[CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES] = (bool) $this->dependencyProvider->getSession()->get('sUserGroupData', ['tax' => 1])['tax'];

        return $userData;
    }

    /**
     * @param string $basketId
     *
     * @return bool
     */
    private function validateBasketExtended($basketId)
    {
        try {
            $basket = $this->loadBasketFromSignature($basketId);
            $this->verifyBasketSignature($basketId, $basket);

            return true;
        } catch (RuntimeException $ex) {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function validateBasketSimple(Order $order)
    {
        $legacyValidator = $this->get('paypal_unified.simple_basket_validator');

        $basket = $this->getBasket();
        $customer = $this->getUser();
        if ($basket === null || $customer === null) {
            return false;
        }

        foreach ($order->getPurchaseUnits() as $purchaseUnit) {
            if (!$legacyValidator->validate($basket, $customer, (float) $purchaseUnit->getAmount()->getValue())) {
                return false;
            }
        }

        return true;
    }
}
