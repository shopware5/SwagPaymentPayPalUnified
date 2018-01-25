<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\Installments\OrderCreditInfoService;
use SwagPaymentPayPalUnified\Components\Services\Installments\ValidationService;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\Installments as InstallmentsSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Credit;

class Installments implements SubscriberInterface
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var ValidationService
     */
    private $validationService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PaymentBuilderInterface
     */
    private $installmentsPaymentBuilder;

    /**
     * @var ExceptionHandlerServiceInterface
     */
    private $exceptionHandlerService;

    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @param SettingsServiceInterface         $settingsService
     * @param ValidationService                $validationService
     * @param Connection                       $connection
     * @param PaymentBuilderInterface          $installmentsPaymentBuilder
     * @param ExceptionHandlerServiceInterface $exceptionHandlerService
     */
    public function __construct(
        SettingsServiceInterface $settingsService,
        ValidationService $validationService,
        Connection $connection,
        PaymentBuilderInterface $installmentsPaymentBuilder,
        ExceptionHandlerServiceInterface $exceptionHandlerService
    ) {
        $this->settingsService = $settingsService;
        $this->validationService = $validationService;
        $this->connection = $connection;
        $this->installmentsPaymentBuilder = $installmentsPaymentBuilder;
        $this->exceptionHandlerService = $exceptionHandlerService;
        $this->paymentMethodProvider = new PaymentMethodProvider();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'onPostDispatchDetail',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [['onPostDispatchCheckout'], ['onConfirmInstallments']],
        ];
    }

    /**
     * @param ActionEventArgs $args
     */
    public function onPostDispatchDetail(ActionEventArgs $args)
    {
        $swUnifiedInstallmentsActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);
        if (!$swUnifiedInstallmentsActive) {
            return;
        }

        /** @var GeneralSettingsModel $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var InstallmentsSettingsModel $installmentsSettings */
        $installmentsSettings = $this->settingsService->getSettings(null, SettingsTable::INSTALLMENTS);
        if (!$installmentsSettings || !$installmentsSettings->getActive()) {
            return;
        }

        $installmentsDisplayKind = $installmentsSettings->getPresentmentTypeDetail();

        if ($installmentsDisplayKind === 0) {
            return;
        }

        $view = $args->getSubject()->View();

        $productPrice = $view->getAssign('sArticle')['price_numeric'];

        if (!$this->validationService->validatePrice($productPrice)) {
            $view->assign('paypalInstallmentsNotAvailable', true);

            return;
        }

        $view->assign('paypalInstallmentsMode', $installmentsDisplayKind === 1 ? 'simple' : 'cheapest');
        $view->assign('paypalInstallmentsProductPrice', $productPrice);
        $view->assign('paypalInstallmentsPageType', 'detail');
    }

    /**
     * @param ActionEventArgs $args
     */
    public function onPostDispatchCheckout(ActionEventArgs $args)
    {
        $action = $args->getRequest()->getActionName();

        if ($action !== 'cart' && $action !== 'confirm') {
            return;
        }

        $swUnifiedInstallmentsActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);
        if (!$swUnifiedInstallmentsActive) {
            return;
        }

        /** @var GeneralSettingsModel $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var InstallmentsSettingsModel $installmentsSettings */
        $installmentsSettings = $this->settingsService->getSettings(null, SettingsTable::INSTALLMENTS);
        if (!$installmentsSettings || !$installmentsSettings->getActive()) {
            return;
        }

        $view = $args->getSubject()->View();
        $selectedPaymentMethodId = (int) $view->getAssign('sPayment')['id'];
        $paymentMethodProvider = new PaymentMethodProvider();
        $installmentsPaymentId = $paymentMethodProvider->getPaymentId($this->connection, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);

        $installmentsDisplayKind = $installmentsSettings->getPresentmentTypeCart();

        //If the selected payment method is Installments, we can not return here, because in any case, the complete financing list should be displayed.
        if ($installmentsDisplayKind === 0 && $selectedPaymentMethodId !== $installmentsPaymentId) {
            return;
        }

        $paymentBuilderParams = new PaymentBuilderParameters();
        $paymentBuilderParams->setBasketData($view->getAssign('sBasket'));
        $paymentBuilderParams->setUserData($view->getAssign('sUserData'));
        /** @var Payment $paymentStruct */
        $paymentStruct = $this->installmentsPaymentBuilder->getPayment($paymentBuilderParams);
        $productPrice = $paymentStruct->getTransactions()->getAmount()->getTotal();

        if (!$this->validationService->validatePrice($productPrice)) {
            return;
        }

        $view->assign('paypalInstallmentsMode', $installmentsDisplayKind === 1 ? 'simple' : 'cheapest');
        $view->assign('paypalInstallmentsProductPrice', $productPrice);
        $view->assign('paypalInstallmentsPageType', 'cart');

        /*
        * If paypal installments is currently selected, we can request all financing information from the api.
        * A complete new template will then be loaded.
        */
        if ($action === 'confirm' && $selectedPaymentMethodId === $installmentsPaymentId) {
            $view->assign('paypalInstallmentsRequestCompleteList', true);
        }
    }

    /**
     * Fetches data for the installments finishing process.
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onConfirmInstallments(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->getSubject();

        /** @var \Enlight_Controller_Request_Request $request */
        $request = $controller->Request();
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');
        $basketId = $request->get('basketId');
        $installmentsFlag = $request->get('installments');

        if (!$installmentsFlag || $paymentId === null || $payerId === null || $request->getActionName() !== 'confirm') {
            return;
        }

        /** @var \Enlight_View_Default $view */
        $view = $controller->View();

        /** @var PaymentResource $paymentResource */
        $paymentResource = $args->getSubject()->get('paypal_unified.payment_resource');

        try {
            $payment = $paymentResource->get($paymentId);
            $view->assign('paypalInstallmentsCredit', $payment['credit_financing_offered']);
            $view->assign('paypalInstallmentsPaymentId', $paymentId);
            $view->assign('paypalInstallmentsPayerId', $payerId);
            $view->assign('paypalInstallmentsBasketId', $basketId);

            $creditStruct = Credit::fromArray($payment['credit_financing_offered']);

            /** @var OrderCreditInfoService $creditInfoService */
            $creditInfoService = $controller->get('paypal_unified.installments.order_credit_info_service');
            $creditInfoService->saveCreditInfo($creditStruct, $payment['id']);

            //Load the custom confirm page
            $view->loadTemplate('frontend/paypal_unified/installments/return/confirm.tpl');
        } catch (\Exception $e) {
            $error = $this->exceptionHandlerService->handle($e, 'get installments information');

            $controller->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'shippingPayment',
                'paypal_unified_error_code' => '5', //Installments error
                'paypal_unified_error_message' => $error->getMessage(),
                'paypal_unified_error_name' => $error->getName(),
            ]);
        }
    }
}
