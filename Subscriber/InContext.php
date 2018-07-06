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
     * @param Connection               $connection
     * @param SettingsServiceInterface $settingsService
     * @param DependencyProvider       $dependencyProvider
     */
    public function __construct(
        Connection $connection,
        SettingsServiceInterface $settingsService,
        DependencyProvider $dependencyProvider
    ) {
        $this->paymentMethodProvider = new PaymentMethodProvider();
        $this->connection = $connection;
        $this->settingsService = $settingsService;
        $this->dependencyProvider = $dependencyProvider;
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

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function addInContextButton(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $controller */
        $controller = $args->getSubject();
        $action = $controller->Request()->getActionName();

        if ($action !== 'confirm') {
            return;
        }

        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel $settings */
        $settings = $this->settingsService->getSettings();
        if (!$settings || !$settings->getActive() || !$settings->getUseInContext()) {
            return;
        }

        /** @var ExpressSettingsModel $expressSettings */
        $expressSettings = $this->settingsService->getSettings(null, SettingsTable::EXPRESS_CHECKOUT);
        if (!$expressSettings) {
            return;
        }

        $view = $controller->View();
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId($this->connection));
        $view->assign('paypalUnifiedModeSandbox', $settings->getSandbox());
        $view->assign('paypalUnifiedUseInContext', $settings->getUseInContext());
        $view->assign('paypalUnifiedEcButtonStyleColor', $expressSettings->getButtonStyleColor());
        $view->assign('paypalUnifiedEcButtonStyleShape', $expressSettings->getButtonStyleShape());
        $view->assign('paypalUnifiedEcButtonStyleSize', $expressSettings->getButtonStyleSize());
        $view->assign('paypalUnifiedLanguageIso', $this->getExpressCheckoutButtonLanguage());
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function addInContextInfoToRequest(\Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getRequest();
        if ($request->getActionName() === 'payment' &&
            $request->getParam('useInContext') &&
            $args->getResponse()->isRedirect()
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
    private function getExpressCheckoutButtonLanguage()
    {
        $languageIso = $this->dependencyProvider->getShop()->getLocale()->getLocale();

        // use english as default, use german if the locale is from german speaking country (de_DE, de_AT, etc)
        // by now the PPP iFrame does not support other languages
        if (strpos($languageIso, 'de_') === 0) {
            $languageIso = 'de_DE';
        }

        return $languageIso;
    }
}
