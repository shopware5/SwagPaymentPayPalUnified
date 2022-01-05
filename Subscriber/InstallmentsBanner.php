<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Enlight_Controller_Request_Request as Request;
use Enlight_View_Default as View;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class InstallmentsBanner implements SubscriberInterface
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        ContextServiceInterface $contextService,
        PaymentMethodProvider $paymentMethodProvider
    ) {
        $this->settingsService = $settingsService;
        $this->contextService = $contextService;
        $this->paymentMethodProvider = $paymentMethodProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatchSecure',
            'Enlight_Controller_Action_PostDispatchSecure_Widgets' => 'onPostDispatchSecure',
        ];
    }

    public function onPostDispatchSecure(ActionEventArgs $args)
    {
        if (!$this->settingsService->hasSettings()) {
            return;
        }

        $active = (bool) $this->settingsService->get(SettingsServiceInterface::SETTING_ACTIVE);
        if (!$active) {
            return;
        }

        $shopContext = $this->contextService->getShopContext();
        $shopLocale = $shopContext->getShop()->getLocale()->getLocale();
        if ($this->advertiseInstallments($shopLocale) === false) {
            return;
        }

        /** @var View $view */
        $view = $args->getSubject()->View();

        $clientId = $this->settingsService->get(SettingsServiceInterface::SETTING_CLIENT_ID);
        $amount = $this->getAmountForPage($args->getSubject()->Request(), $view);
        $currency = $shopContext->getCurrency()->getCurrency();
        $buyerCountry = $this->getBuyerCountryByCurrencyAndShopCountryIso($currency, $shopLocale);

        $view->assign('paypalUnifiedInstallmentsBanner', true);
        $view->assign('paypalUnifiedInstallmentsBannerClientId', $clientId);
        $view->assign('paypalUnifiedInstallmentsBannerAmount', $amount);
        $view->assign('paypalUnifiedInstallmentsBannerCurrency', $currency);
        $view->assign('paypalUnifiedInstallmentsBannerBuyerCountry', $buyerCountry);
    }

    /**
     * @return float
     */
    private function getAmountForPage(Request $request, View $view)
    {
        $amount = 0.0;
        $controllerName = \strtolower($request->getControllerName());
        $actionName = \strtolower($request->getActionName());
        $validCheckoutActions = ['cart', 'ajaxcart', 'ajax_cart'];

        if ($controllerName === 'detail' && $actionName === 'index') {
            $product = $view->getAssign('sArticle');
            $amount = (float) $product['price_numeric'];
        } elseif ($controllerName === 'checkout' && \in_array($actionName, $validCheckoutActions, true)) {
            $cart = $view->getAssign('sBasket');
            $amount = (float) $cart['AmountNumeric'];
        }

        return $amount;
    }

    /**
     * @param string $countryIso
     *
     * @return bool
     */
    private function isInstallmentsCountry($countryIso)
    {
        $countryCodes = [
            'de_de',
            'en_au',
            'en_gb',
            'en_us',
            'fr_fr',
        ];

        return \in_array(\strtolower($countryIso), $countryCodes, true);
    }

    /**
     * @param string $shopLocale
     *
     * @return bool
     */
    private function advertiseInstallments($shopLocale)
    {
        $isInstallmentsCountry = $this->isInstallmentsCountry($shopLocale);

        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        return $swUnifiedActive
            && (bool) $this->settingsService->get(SettingsServiceInterface::SETTING_ADVERTISE_INSTALLMENTS, SettingsTable::INSTALLMENTS)
            && $isInstallmentsCountry;
    }

    /**
     * @param string $currency
     * @param string $countryIso
     *
     * @return string|null
     */
    private function getBuyerCountryByCurrencyAndShopCountryIso($currency, $countryIso)
    {
        $key = \strtolower(\sprintf('%s_%s', $currency, $countryIso));

        $currencies = [
            'aud_en_au' => 'AU',
            'eur_de_de' => 'DE',
            'eur_fr_fr' => 'FR',
            'gbp_en_gb' => 'GB',
            'usd_en_us' => 'US',
        ];

        if (!isset($currencies[$key])) {
            return null;
        }

        return $currencies[$key];
    }
}
