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

    /**
     * @param SettingsServiceInterface         $settingsService
     * @param Session                          $session
     * @param PaymentResource                  $paymentResource
     * @param PaymentAddressService            $addressRequestService
     * @param PaymentBuilderInterface          $paymentBuilder
     * @param ExceptionHandlerServiceInterface $exceptionHandlerService
     * @param Connection                       $connection
     * @param ClientService                    $clientService
     * @param DependencyProvider               $dependencyProvider
     */
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

    /**
     * @param ActionEventArgs $args
     */
    public function addExpressCheckoutButtonCart(ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var ExpressSettingsModel $expressSettings */
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
        if ($action !== 'cart' &&
            $action !== 'ajaxcart' &&
            $action !== 'ajax_cart' &&
            $action !== 'ajax_add_article' &&
            $action !== 'ajaxaddarticle'
        ) {
            return;
        }

        $cart = $view->getAssign('sBasket');
        $product = $view->getAssign('sArticle'); // content on modal window of ajaxAddArticleAction

        if ((isset($cart['content']) || $product) && !$view->getAssign('sUserLoggedIn')) {
            $view->assign('paypalUnifiedUseInContext', $generalSettings->getUseInContext());
            $this->addEcButtonStyleInfo($view, $expressSettings);
        }
    }

    /**
     * @param ActionEventArgs $args
     */
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

    /**
     * @param ActionEventArgs $args
     */
    public function addPaymentInfoToRequest(ActionEventArgs $args)
    {
        $request = $args->getRequest();

        if (strtolower($request->getActionName()) === 'payment' &&
            $request->getParam('expressCheckout', false) &&
            $args->getResponse()->isRedirect()
        ) {
            $paymentId = $request->getParam('paymentId');

            $this->patchAddressAndAmount($paymentId);

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

    /**
     * @param ActionEventArgs $args
     */
    public function addExpressCheckoutButtonDetail(ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var ExpressSettingsModel $expressSettings */
        $expressSettings = $this->settingsService->getSettings(null, SettingsTable::EXPRESS_CHECKOUT);
        if (!$expressSettings || !$expressSettings->getDetailActive()) {
            return;
        }

        $view = $args->getSubject()->View();

        if (!$view->getAssign('userLoggedIn')) {
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

        /** @var GeneralSettingsModel $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var ExpressSettingsModel $expressSettings */
        $expressSettings = $this->settingsService->getSettings(null, SettingsTable::EXPRESS_CHECKOUT);
        if (!$expressSettings || !$expressSettings->getListingActive()) {
            return;
        }

        $view = $args->getSubject()->View();

        if (!$view->getAssign('userLoggedIn')) {
            $view->assign('paypalUnifiedEcListingActive', true);
            $this->addEcButtonBehaviour($view, $generalSettings);
            $this->addEcButtonStyleInfo($view, $expressSettings);
            $view->assign('paypalUnifiedEcButtonStyleSize', 'small');
        }
    }

    /**
     * @param ActionEventArgs $args
     */
    public function addExpressCheckoutButtonLogin(ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var ExpressSettingsModel $expressSettings */
        $expressSettings = $this->settingsService->getSettings(null, SettingsTable::EXPRESS_CHECKOUT);
        if (!$expressSettings || !$expressSettings->getLoginActive()) {
            return;
        }

        $view = $args->getSubject()->View();
        $requestParams = $args->getRequest()->getParams();

        if ($requestParams['sTarget'] === 'checkout' && $requestParams['sTargetAction'] === 'confirm') {
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
     *
     * @throws Exception
     */
    private function patchAddressAndAmount($paymentId)
    {
        $orderVariables = $this->session->get('sOrderVariables');
        $userData = $orderVariables['sUserData'];
        $basketData = $orderVariables['sBasket'];

        $shippingAddress = $this->paymentAddressService->getShippingAddress($userData);
        $addressPatch = new PaymentAddressPatch($shippingAddress);

        $requestParams = new PaymentBuilderParameters();
        $requestParams->setBasketData($basketData);
        $requestParams->setUserData($userData);

        $paymentStruct = $this->paymentBuilder->getPayment($requestParams);
        $amountPatch = new PaymentAmountPatch($paymentStruct->getTransactions()->getAmount());

        $this->clientService->setPartnerAttributionId(PartnerAttributionId::PAYPAL_EXPRESS_CHECKOUT);

        try {
            if ($this->settingsService->get('submit_cart', SettingsTable::EXPRESS_CHECKOUT)) {
                $itemListPatch = new PaymentItemsPatch($paymentStruct->getTransactions()->getItemList()->getItems());
                $this->paymentResource->patch($paymentId, [$addressPatch, $amountPatch, $itemListPatch]);

                return;
            }

            $this->paymentResource->patch($paymentId, [$addressPatch, $amountPatch]);
        } catch (Exception $exception) {
            $this->exceptionHandlerService->handle($exception, 'patch the payment for express checkout');
            throw $exception;
        }
    }

    /**
     * @return string
     */
    private function getExpressCheckoutButtonLanguage()
    {
        return $this->dependencyProvider->getShop()->getLocale()->getLocale();
    }

    /**
     * @param ViewEngine           $view
     * @param GeneralSettingsModel $generalSettings
     */
    private function addEcButtonBehaviour(ViewEngine $view, GeneralSettingsModel $generalSettings)
    {
        $view->assign('paypalUnifiedModeSandbox', $generalSettings->getSandbox());
        $view->assign('paypalUnifiedUseInContext', $generalSettings->getUseInContext());
    }

    /**
     * @param ViewEngine           $view
     * @param ExpressSettingsModel $expressSettings
     */
    private function addEcButtonStyleInfo(ViewEngine $view, ExpressSettingsModel $expressSettings)
    {
        $view->assign('paypalUnifiedEcButtonStyleColor', $expressSettings->getButtonStyleColor());
        $view->assign('paypalUnifiedEcButtonStyleShape', $expressSettings->getButtonStyleShape());
        $view->assign('paypalUnifiedEcButtonStyleSize', $expressSettings->getButtonStyleSize());
        $view->assign('paypalUnifiedLanguageIso', $this->getExpressCheckoutButtonLanguage());
    }
}
