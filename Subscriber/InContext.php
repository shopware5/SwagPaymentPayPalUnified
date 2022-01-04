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
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Models\Settings\ExpressCheckout as ExpressSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class InContext implements SubscriberInterface
{
    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @var Connection
     */
    private $connection;

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
        Connection $connection,
        SettingsServiceInterface $settingsService,
        DependencyProvider $dependencyProvider,
        PaymentMethodProvider $paymentMethodProvider,
        ContextServiceInterface $contextService
    ) {
        $this->connection = $connection;
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

        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel|null $settings */
        $settings = $this->settingsService->getSettings();
        if (!$settings
            || !$settings->getActive()
            || !$settings->getUseInContext()
            || ($settings->getUseSmartPaymentButtons() && $settings->getMerchantLocation() === GeneralSettingsModel::MERCHANT_LOCATION_OTHER)
        ) {
            return;
        }

        /** @var ExpressSettingsModel|null $expressSettings */
        $expressSettings = $this->settingsService->getSettings(null, SettingsTable::EXPRESS_CHECKOUT);
        if (!$expressSettings) {
            return;
        }

        $view = $controller->View();
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME));
        $view->assign('paypalUnifiedModeSandbox', $settings->getSandbox());
        $view->assign('paypalUnifiedUseInContext', $settings->getUseInContext());
        $view->assign('paypalUnifiedEcButtonStyleColor', $expressSettings->getButtonStyleColor());
        $view->assign('paypalUnifiedEcButtonStyleShape', $expressSettings->getButtonStyleShape());
        $view->assign('paypalUnifiedEcButtonStyleSize', $expressSettings->getButtonStyleSize());
        $view->assign('paypalUnifiedLanguageIso', $this->getInContextButtonLanguage($expressSettings));
        $view->assign('paypalUnifiedClientId', $settings->getClientId());
        $view->assign('paypalUnifiedCurrency', $this->contextService->getContext()->getCurrency()->getCurrency());
        $view->assign('paypalUnifiedIntent', $this->settingsService->get(SettingsServiceInterface::SETTING_INTENT));
    }

    public function addInContextInfoToRequest(\Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getRequest();
        if ($request->getActionName() === 'payment'
            && $request->getParam('useInContext')
            && $args->getResponse()->isRedirect()
        ) {
            $args->getSubject()->redirect([
                'controller' => 'PaypalUnified',
                'action' => 'gateway',
                'useInContext' => true,
            ]);
        }
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
