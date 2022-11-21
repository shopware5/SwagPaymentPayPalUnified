<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Components_Session_Namespace as Session;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Enlight_View_Default as ViewEngine;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\ButtonLocaleService;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\EsdProductCheckerInterface;
use SwagPaymentPayPalUnified\Models\Settings\ExpressCheckout as ExpressSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use UnexpectedValueException;

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
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var EsdProductCheckerInterface
     */
    private $esdProductChecker;

    /**
     * @var ButtonLocaleService
     */
    private $buttonLocaleService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        Session $session,
        DependencyProvider $dependencyProvider,
        EsdProductCheckerInterface $esdProductChecker,
        PaymentMethodProviderInterface $paymentMethodProvider,
        ButtonLocaleService $buttonLocaleService
    ) {
        $this->settingsService = $settingsService;
        $this->session = $session;
        $this->dependencyProvider = $dependencyProvider;
        $this->esdProductChecker = $esdProductChecker;
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->buttonLocaleService = $buttonLocaleService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [
                ['addExpressOrderInfoOnConfirm'],
            ],
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'addExpressCheckoutButtonCart',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'addExpressCheckoutButtonDetail',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Listing' => 'addExpressCheckoutButtonListing',
            'Enlight_Controller_Action_PreDispatch_Widgets_Listing' => 'addExpressCheckoutButtonListing',
            'Enlight_Controller_Action_PostDispatch_Frontend_Register' => 'addExpressCheckoutButtonLogin', // cannot use "secure" here, because it's forwarded call from checkout/confirm
        ];
    }

    /**
     * @return void
     */
    public function addExpressCheckoutButtonCart(ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
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

        $cart = $view->getAssign('sBasket');
        if ($cart === null) {
            $cart = $this->dependencyProvider->getModule('sBasket')->sGetBasket();
        }

        $cartProductIds = $this->getProductIdsFromBasket($cart['content']);
        if ($cartProductIds === []) {
            return;
        }

        if ($this->esdProductChecker->checkForEsdProducts($cartProductIds) === true) {
            return;
        }

        $view->assign('paypalUnifiedEcCartActive', $expressSettings->getCartActive());
        $view->assign('paypalUnifiedEcOffCanvasActive', $expressSettings->getOffCanvasActive());

        $request = $args->getRequest();
        $controller = \strtolower($request->getControllerName());
        if ($controller !== 'checkout') {
            return;
        }

        $action = \strtolower($request->getActionName());
        $allowedActions = ['cart', 'ajaxcart', 'ajax_cart', 'ajax_add_article', 'ajaxaddarticle'];
        if (!\in_array($action, $allowedActions, true)) {
            return;
        }

        $product = $view->getAssign('sArticle'); // content on modal window of ajaxAddArticleAction

        if ((isset($cart['content']) || $product) && !$this->isUserLoggedIn()) {
            $view->assign('paypalUnifiedUseInContext', $generalSettings->getUseInContext());
            $this->addEcButtonBehaviour($view, $generalSettings);
            $this->addEcButtonStyleInfo($view, $expressSettings, $generalSettings);
        }
    }

    /**
     * @return void
     */
    public function addExpressOrderInfoOnConfirm(ActionEventArgs $args)
    {
        $request = $args->getRequest();
        $view = $args->getSubject()->View();

        $cart = $view->getAssign('sBasket');
        $cartProductIds = $this->getProductIdsFromBasket($cart['content']);
        if ($cartProductIds === []) {
            return;
        }
        if ($this->esdProductChecker->checkForEsdProducts($cartProductIds) === true) {
            return;
        }

        if (\strtolower($request->getActionName()) === 'confirm' && $request->getParam('expressCheckout', false)) {
            $view->assign('paypalUnifiedExpressCheckout', true);
            $view->assign('paypalUnifiedExpressOrderId', $request->getParam('paypalOrderId'));
            $view->assign('paypalUnifiedExpressPayerId', $request->getParam('payerId'));
            $view->assign('paypalUnifiedExpressBasketId', $request->getParam('basketId'));
        }

        if (\strtolower($request->getActionName()) === 'payment' && $request->getParam('expressCheckout', false) && $args->getResponse()->isRedirect()) {
            $args->getSubject()->redirect([
                'controller' => 'PaypalUnifiedV2ExpressCheckout',
                'action' => 'expressCheckoutFinish',
                'paypalOrderId' => $request->getParam('paypalOrderId'),
            ]);
        }
    }

    /**
     * @return void
     */
    public function addExpressCheckoutButtonDetail(ActionEventArgs $args)
    {
        if ($args->getSubject()->View()->getAssign('sArticle')['esd'] === true) {
            return;
        }

        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
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
            $this->addEcButtonStyleInfo($view, $expressSettings, $generalSettings);
        }
    }

    /**
     * @return void
     */
    public function addExpressCheckoutButtonListing(ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
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

        if ($this->isUserLoggedIn()) {
            return;
        }

        $view = $args->getSubject()->View();

        $categoryId = (int) $args->getSubject()->Request()->getParam('sCategory');

        $esdProductNumbers = $this->esdProductChecker->getEsdProductNumbers($categoryId);

        $view->assign('paypalUnifiedEsdProducts', \json_encode($esdProductNumbers));
        $view->assign('paypalUnifiedEcListingActive', true);
        $this->addEcButtonBehaviour($view, $generalSettings);
        $this->addEcButtonStyleInfo($view, $expressSettings, $generalSettings);
        $view->assign('paypalUnifiedEcButtonStyleSize', 'small');
    }

    /**
     * @return void
     */
    public function addExpressCheckoutButtonLogin(ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
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

        $sBasket = $this->dependencyProvider->getModule('Basket')->sGetBasket();
        $productIds = $this->getProductIdsFromBasket($sBasket['content']);
        if ($this->esdProductChecker->checkForEsdProducts($productIds) === true) {
            return;
        }

        $targetAction = $requestParams['sTargetAction'];
        if ($requestParams['sTarget'] === 'checkout' && ($targetAction === 'confirm' || $targetAction === 'shippingPayment')) {
            $view->assign('paypalUnifiedEcLoginActive', true);
            $this->addEcButtonBehaviour($view, $generalSettings);
            $this->addEcButtonStyleInfo($view, $expressSettings, $generalSettings);
        }
    }

    /**
     * @return void
     */
    private function addEcButtonBehaviour(ViewEngine $view, GeneralSettingsModel $generalSettings)
    {
        $view->assign('paypalUnifiedClientId', $generalSettings->getSandbox() ? $generalSettings->getSandboxClientId() : $generalSettings->getClientId());

        $shop = $this->dependencyProvider->getShop();

        if (!$shop instanceof Shop) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s, got null.', Shop::class));
        }

        $view->assign('paypalUnifiedCurrency', $shop->getCurrency()->getCurrency());
    }

    /**
     * @return void
     */
    private function addEcButtonStyleInfo(ViewEngine $view, ExpressSettingsModel $expressSettings, GeneralSettingsModel $generalSettings)
    {
        $view->assign([
            'paypalUnifiedEcButtonStyleColor' => $expressSettings->getButtonStyleColor(),
            'paypalUnifiedEcButtonStyleShape' => $expressSettings->getButtonStyleShape(),
            'paypalUnifiedEcButtonStyleSize' => $expressSettings->getButtonStyleSize(),
            'paypalUnifiedButtonLocale' => $this->buttonLocaleService->getButtonLocale($generalSettings->getButtonLocale()),
            'paypalUnifiedIntent' => $generalSettings->getIntent(),
        ]);
    }

    /**
     * @return bool
     */
    private function isUserLoggedIn()
    {
        return (bool) $this->session->get('sUserId');
    }

    /**
     * @param array<array<string, mixed>>|null $content
     *
     * @return array<int>
     */
    private function getProductIdsFromBasket($content)
    {
        if ($content === null) {
            return [];
        }

        return array_map(function (array $product) {
            return (int) $product['articleID'];
        }, $content);
    }
}
