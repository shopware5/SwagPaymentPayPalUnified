<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Models\Settings\ExpressCheckout as ExpressSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class InContext implements SubscriberInterface
{
    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        DependencyProvider $dependencyProvider,
        PaymentMethodProviderInterface $paymentMethodProvider,
        ContextServiceInterface $contextService
    ) {
        $this->settingsService = $settingsService;
        $this->dependencyProvider = $dependencyProvider;
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->contextService = $contextService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [
                ['addInContextButton'],
                ['addInfoToPaymentRequest'],
                ['addInContextInfoToRequest', 100],
            ],
        ];
    }

    public function addInContextButton(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $controller */
        $controller = $args->getSubject();
        $action = $controller->Request()->getActionName();

        if ($action !== 'confirm') {
            return;
        }

        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel|null $settings */
        $settings = $this->settingsService->getSettings();
        if (!$settings
            || !$settings->getActive()
            || !$settings->getUseInContext()
            || $settings->getUseSmartPaymentButtons()
        ) {
            return;
        }

        /** @var ExpressSettingsModel|null $expressSettings */
        $expressSettings = $this->settingsService->getSettings(null, SettingsTable::EXPRESS_CHECKOUT);
        if (!$expressSettings) {
            return;
        }

        $sandbox = $settings->getSandbox();
        $view = $controller->View();
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME));
        $view->assign('paypalUnifiedModeSandbox', $sandbox);
        $view->assign('paypalUnifiedUseInContext', $settings->getUseInContext());
        $view->assign('paypalUnifiedButtonStyleColor', $settings->getButtonStyleColor());
        $view->assign('paypalUnifiedButtonStyleShape', $settings->getButtonStyleShape());
        $view->assign('paypalUnifiedButtonStyleSize', $settings->getButtonStyleSize());
        $view->assign('paypalUnifiedButtonLocale', (string) $settings->getButtonLocale());
        $view->assign('paypalUnifiedLanguageIso', $this->getInContextButtonLanguage($expressSettings));
        $view->assign('paypalUnifiedClientId', $sandbox ? $settings->getSandboxClientId() : $settings->getClientId());
        $view->assign('paypalUnifiedCurrency', $this->contextService->getContext()->getCurrency()->getCurrency());
        $view->assign('paypalUnifiedIntent', $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_INTENT));
    }

    public function addInContextInfoToRequest(\Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getRequest();
        $view = $args->getSubject()->View();

        if ($request->getActionName() === 'payment'
            && $request->getParam('useInContext')
            && $args->getResponse()->isRedirect()
        ) {
            $args->getSubject()->redirect([
                'controller' => 'PaypalUnifiedV2',
                'action' => 'return',
                'useInContext' => true,
            ]);
        } elseif (\strtolower($request->getActionName()) === 'confirm' && $request->getParam('inContextCheckout', false)) {
            // This determines, whether the paypal-Buttons need to be rendered
            $view->assign('paypalUnifiedInContextCheckout', true);
            $view->assign('paypalUnifiedInContextOrderId', $request->getParam('orderId'));
            $view->assign('paypalUnifiedInContextPayerId', $request->getParam('payerId'));
            $view->assign('paypalUnifiedInContextBasketId', $request->getParam('basketId'));
        }
    }

    /**
     * @return void
     */
    public function addInfoToPaymentRequest(\Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getRequest();

        if (\strtolower($request->getActionName()) !== 'payment'
            || !$request->getParam('inContextCheckout', false)
            || !$args->getResponse()->isRedirect()
        ) {
            return;
        }

        $args->getSubject()->redirect([
            'controller' => 'PaypalUnifiedV2',
            'action' => 'return',
            'inContextCheckout' => true,
            'token' => $request->getParam('orderId'),
            'PayerID' => $request->getParam('payerId'),
            'basketId' => $request->getParam('basketId'),
        ]);
    }

    /**
     * @return string
     */
    private function getInContextButtonLanguage(ExpressSettingsModel $expressSettings)
    {
        $shop = $this->dependencyProvider->getShop();

        if (!$shop instanceof Shop) {
            throw new \UnexpectedValueException(sprintf('Tried to access %s, but it\'s not set in the DIC.', Shop::class));
        }

        $locale = $shop->getLocale()->getLocale();
        $buttonLocaleFromSetting = (string) $expressSettings->getButtonLocale();

        if ($buttonLocaleFromSetting !== '') {
            $locale = $buttonLocaleFromSetting;
        }

        return $locale;
    }
}
