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
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacadeInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\PaymentControllerHelper;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrderBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;

class Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice extends Shopware_Controllers_Frontend_Payment
{
    const MAXIMUM_RETRIES = 32;
    const TIMEOUT = \CURLOPT_TIMEOUT * 2;
    const SLEEP = 1;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var PaymentControllerHelper
     */
    private $paymentControllerHelper;

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
     * @var PayPalOrderParameterFacadeInterface
     */
    private $payPalOrderParameterFacade;

    /**
     * @var OrderDataService
     */
    private $orderDataService;

    public function preDispatch()
    {
        $this->dependencyProvider = $this->container->get('paypal_unified.dependency_provider');
        $this->paymentControllerHelper = $this->container->get('paypal_unified.payment_controller_helper');
        $this->orderBuilderService = $this->container->get('paypal_unified.paypal_order_builder_service');
        $this->orderResource = $this->container->get('paypal_unified.v2.order_resource');
        $this->redirectDataBuilderFactory = $this->container->get('paypal_unified.redirect_data_builder_factory');
        $this->payPalOrderParameterFacade = $this->get('paypal_unified.paypal_order_parameter_facade');
        $this->orderDataService = $this->get('paypal_unified.order_data_service');

        parent::preDispatch();
    }

    public function indexAction()
    {
        $session = $this->dependencyProvider->getSession();
        $shopwareSessionOrderData = $session->get('sOrderVariables');

        $shopwareOrderData = new ShopwareOrderData($shopwareSessionOrderData['sUserData'], $shopwareSessionOrderData['sBasket']);

        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2, $shopwareOrderData);

        try {
            $orderData = $this->orderBuilderService->getOrder($orderParams);

            // We set a random invoice ID here, it will be updated later.
            $orderData->getPurchaseUnits()[0]->setInvoiceId(bin2hex((string) \openssl_random_pseudo_bytes(8)));

            $paypalOrder = $this->orderResource->create($orderData, PartnerAttributionId::PAYPAL_ALL_V2, false);
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

        $this->createShopwareOrder($paypalOrder->getId());

        // TODO: (PT-12529) Implement webhook solution
        // TODO: (PT-12529) Send response, implement a "waiting" template using a widget controller (which reacts to the webhook call OR does the polling) + AJAX for local polling
        if ($this->isPaymentCompleted($paypalOrder->getId())) {
            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $paypalOrder->getId(),
            ]);
        }
    }

    /**
     * @param string $payPalOrderId
     *
     * @return bool indicating whether the payment is complete
     */
    private function isPaymentCompleted($payPalOrderId)
    {
        $start = time();

        for ($i = 0; $i <= self::MAXIMUM_RETRIES; ++$i) {
            $paypalOrder = $this->orderResource->get($payPalOrderId);

            if ($paypalOrder->getStatus() === PaymentStatusV2::ORDER_COMPLETED) {
                return true;
            }

            if ($i >= self::MAXIMUM_RETRIES || time() >= $start + self::TIMEOUT) {
                $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                    ->setCode(ErrorCodes::COMMUNICATION_FAILURE)
                    ->setException(new \RuntimeException('Maximum retries exceeded.'));

                $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

                break;
            } elseif ($paypalOrder->getStatus() === PaymentStatusV2::ORDER_AUTHORIZATION_DENIED) {
                $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
                    ->setCode(ErrorCodes::UNKNOWN)
                    ->setException(new \RuntimeException('Order has not been authorised.'));

                $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);

                break;
            }

            sleep(self::SLEEP);
        }

        return false;
    }

    /**
     * @param string $payPalOrderId
     *
     * @return string
     */
    private function createShopwareOrder($payPalOrderId)
    {
        $orderNumber = (string) $this->saveOrder($payPalOrderId, $payPalOrderId, PaymentStatus::PAYMENT_STATUS_OPEN);

        $this->orderDataService->applyPaymentTypeAttribute($orderNumber, PaymentType::PAYPAL_PAY_UPON_INVOICE_V2);

        return $orderNumber;
    }
}
