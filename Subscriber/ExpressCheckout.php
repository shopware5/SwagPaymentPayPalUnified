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
use Enlight_Components_Session_Namespace as Session;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Enlight_View_Default as ViewEngine;
use Exception;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\PaymentAddressService;
use SwagPaymentPayPalUnified\Models\Settings\ExpressCheckout as ExpressSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAddressPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAmountPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentItemsPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;

class ExpressCheckout implements SubscriberInterface
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var PaymentResource
     */
    private $paymentResource;

    /**
     * @var PaymentAddressService
     */
    private $paymentAddressService;

    /**
     * @var PaymentBuilderInterface
     */
    private $paymentBuilder;

    /**
     * @var ExceptionHandlerServiceInterface
     */
    private $exceptionHandlerService;

    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    public function __construct(
        SettingsServiceInterface $settingsService,
        Session $session,
        PaymentResource $paymentResource,
        PaymentAddressService $addressRequestService,
        PaymentBuilderInterface $paymentBuilder,
        ExceptionHandlerServiceInterface $exceptionHandlerService,
        Connection $connection,
        ClientService $clientService,
        DependencyProvider $dependencyProvider
    ) {
        $this->settingsService = $settingsService;
        $this->session = $session;
        $this->paymentResource = $paymentResource;
        $this->paymentAddressService = $addressRequestService;
        $this->paymentBuilder = $paymentBuilder;
        $this->exceptionHandlerService = $exceptionHandlerService;
        $this->paymentMethodProvider = new PaymentMethodProvider();
        $this->connection = $connection;
        $this->clientService = $clientService;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'addExpressCheckoutButtonCart',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [
                ['addEcInfoOnConfirm'],
                ['addPaymentInfoToRequest', 100],
            ],
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'addExpressCheckoutButtonDetail',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Listing' => 'addExpressCheckoutButtonListing',
            'Enlight_Controller_Action_PreDispatch_Widgets_Listing' => 'addExpressCheckoutButtonListing',
            'Enlight_Controller_Action_PostDispatch_Frontend_Register' => 'addExpressCheckoutButtonLogin', // cannot use "secure" here, because it's forwarded call from checkout/confirm
        ];
    }

    public function addExpressCheckoutButtonCart(ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel|null $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var ExpressSettingsModel|null $expressSettings */
        $expressSettings = $this->settingsService->getSettings(null, SettingsTable::EXPRESS_CHECKOUT);
        if (!($expressSettings instanceof ExpressSettingsModel)) {
            return;
        }

        $view = $args->getSubject()->View();
        $view->assign('paypalUnifiedEcCartActive', $expressSettings->getCartActive());
        $view->assign('paypalUnifiedModeSandbox', $generalSettings->getSandbox());
        $view->assign('paypalUnifiedEcOffCanvasActive', $expressSettings->getOffCanvasActive());

        $request = $args->getRequest();
        $controller = strtolower($request->getControllerName());
        if ($controller !== 'checkout') {
            return;
        }

        $action = strtolower($request->getActionName());
        $allowedActions = ['cart', 'ajaxcart', 'ajax_cart', 'ajax_add_article', 'ajaxaddarticle'];
        if (!in_array($action, $allowedActions, true)) {
            return;
        }

        $cart = $view->getAssign('sBasket');
        $product = $view->getAssign('sArticle'); // content on modal window of ajaxAddArticleAction

        if ((isset($cart['content']) || $product) && !$this->isUserLoggedIn()) {
            $view->assign('paypalUnifiedUseInContext', $generalSettings->getUseInContext());
            $this->addEcButtonStyleInfo($view, $expressSettings);
        }
    }

    public function addEcInfoOnConfirm(ActionEventArgs $args)
    {
        $request = $args->getRequest();
        $view = $args->getSubject()->View();

        if (strtolower($request->getActionName()) === 'confirm' && $request->getParam('expressCheckout', false)) {
            $view->assign('paypalUnifiedExpressCheckout', true);
            $view->assign('paypalUnifiedExpressPaymentId', $request->getParam('paymentId'));
            $view->assign('paypalUnifiedExpressPayerId', $request->getParam('payerId'));
            $view->assign('paypalUnifiedExpressBasketId', $request->getParam('basketId'));
        }
    }

    public function addPaymentInfoToRequest(ActionEventArgs $args)
    {
        $request = $args->getRequest();

        if (strtolower($request->getActionName()) === 'payment'
            && $request->getParam('expressCheckout', false)
            && $args->getResponse()->isRedirect()
        ) {
            $paymentId = $request->getParam('paymentId');

            try {
                $this->patchAddressAndAmount($paymentId);
            } catch (Exception $exception) {
                $redirectData = $this->handlePaymentPatchException($exception);
                $args->getSubject()->redirect($redirectData);

                return;
            }

            $args->getSubject()->redirect([
                'controller' => 'PaypalUnified',
                'action' => 'return',
                'expressCheckout' => true,
                'paymentId' => $paymentId,
                'PayerID' => $request->getParam('payerId'),
                'basketId' => $request->getParam('basketId'),
            ]);
        }
    }

    public function addExpressCheckoutButtonDetail(ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel|null $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var ExpressSettingsModel|null $expressSettings */
        $expressSettings = $this->settingsService->getSettings(null, SettingsTable::EXPRESS_CHECKOUT);
        if (!$expressSettings || !$expressSettings->getDetailActive()) {
            return;
        }

        $view = $args->getSubject()->View();

        if (!$this->isUserLoggedIn()) {
            $view->assign('paypalUnifiedEcDetailActive', true);
            $this->addEcButtonBehaviour($view, $generalSettings);
            $this->addEcButtonStyleInfo($view, $expressSettings);
        }
    }

    public function addExpressCheckoutButtonListing(ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel|null $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var ExpressSettingsModel|null $expressSettings */
        $expressSettings = $this->settingsService->getSettings(null, SettingsTable::EXPRESS_CHECKOUT);
        if (!$expressSettings || !$expressSettings->getListingActive()) {
            return;
        }

        $view = $args->getSubject()->View();

        if (!$this->isUserLoggedIn()) {
            $view->assign('paypalUnifiedEcListingActive', true);
            $this->addEcButtonBehaviour($view, $generalSettings);
            $this->addEcButtonStyleInfo($view, $expressSettings);
            $view->assign('paypalUnifiedEcButtonStyleSize', 'small');
        }
    }

    public function addExpressCheckoutButtonLogin(ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel|null $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var ExpressSettingsModel|null $expressSettings */
        $expressSettings = $this->settingsService->getSettings(null, SettingsTable::EXPRESS_CHECKOUT);
        if (!$expressSettings || !$expressSettings->getLoginActive()) {
            return;
        }

        $view = $args->getSubject()->View();
        $requestParams = $args->getRequest()->getParams();

        $targetAction = $requestParams['sTargetAction'];
        if ($requestParams['sTarget'] === 'checkout' && ($targetAction === 'confirm' || $targetAction === 'shippingPayment')) {
            $view->assign('paypalUnifiedEcLoginActive', true);
            $this->addEcButtonBehaviour($view, $generalSettings);
            $this->addEcButtonStyleInfo($view, $expressSettings);
        }
    }

    /**
     * before the express checkout payment can be executed, the address and amount, which contains the shipping costs,
     * must be updated, because they may have changed during the process
     *
     * @param string $paymentId
     */
    private function patchAddressAndAmount($paymentId)
    {
        $orderVariables = $this->session->get('sOrderVariables');
        $userData = $orderVariables['sUserData'];
        $basketData = $orderVariables['sBasket'];
        $userData[PaymentBuilderInterface::CUSTOMER_GROUP_USE_GROSS_PRICES] = (bool) $this->dependencyProvider->getSession()
            ->get('sUserGroupData', ['tax' => 1])['tax'];

        $shippingAddress = $this->paymentAddressService->getShippingAddress($userData);
        $addressPatch = new PaymentAddressPatch($shippingAddress);

        $requestParams = new PaymentBuilderParameters();
        $requestParams->setBasketData($basketData);
        $requestParams->setUserData($userData);
        $requestParams->setPaymentType(PaymentType::PAYPAL_EXPRESS);

        $paymentStruct = $this->paymentBuilder->getPayment($requestParams);
        $amountPatch = new PaymentAmountPatch($paymentStruct->getTransactions()->getAmount());

        $this->clientService->setPartnerAttributionId(PartnerAttributionId::PAYPAL_EXPRESS_CHECKOUT);

        $patches = [$addressPatch, $amountPatch];
        $itemList = $paymentStruct->getTransactions()->getItemList();
        if ($itemList !== null) {
            $patches[] = new PaymentItemsPatch($itemList->getItems());
        }

        $this->paymentResource->patch($paymentId, $patches);
    }

    /**
     * @return string
     */
    private function getExpressCheckoutButtonLanguage(ExpressSettingsModel $expressSettings)
    {
        $locale = $this->dependencyProvider->getShop()->getLocale()->getLocale();
        $buttonLocaleFromSetting = (string) $expressSettings->getButtonLocale();

        if ($buttonLocaleFromSetting !== '') {
            $locale = $buttonLocaleFromSetting;
        }

        return $locale;
    }

    private function addEcButtonBehaviour(ViewEngine $view, GeneralSettingsModel $generalSettings)
    {
        $view->assign('paypalUnifiedModeSandbox', $generalSettings->getSandbox());
        $view->assign('paypalUnifiedUseInContext', $generalSettings->getUseInContext());
    }

    private function addEcButtonStyleInfo(ViewEngine $view, ExpressSettingsModel $expressSettings)
    {
        $view->assign('paypalUnifiedEcButtonStyleColor', $expressSettings->getButtonStyleColor());
        $view->assign('paypalUnifiedEcButtonStyleShape', $expressSettings->getButtonStyleShape());
        $view->assign('paypalUnifiedEcButtonStyleSize', $expressSettings->getButtonStyleSize());
        $view->assign('paypalUnifiedLanguageIso', $this->getExpressCheckoutButtonLanguage($expressSettings));
    }

    /**
     * @return array
     */
    private function handlePaymentPatchException(Exception $exception)
    {
        $message = null;
        $name = null;
        $error = $this->exceptionHandlerService->handle($exception, 'patch the payment for express checkout');

        if ($this->settingsService->hasSettings() && $this->settingsService->get('display_errors')) {
            $message = $error->getMessage();
            $name = $error->getName();
        }

        $redirectData = [
            'controller' => 'checkout',
            'action' => 'shippingPayment',
            'paypal_unified_error_code' => ErrorCodes::COMMUNICATION_FAILURE,
        ];

        if ($name !== null) {
            $redirectData['paypal_unified_error_name'] = $name;
            $redirectData['paypal_unified_error_message'] = $message;
        }

        return $redirectData;
    }

    private function isUserLoggedIn()
    {
        return (bool) $this->session->get('sUserId');
    }
}
